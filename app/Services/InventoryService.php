<?php

namespace App\Services;

use App\Enums\InventoryMovementType;
use App\Models\InventoryItem;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function initialize(ProductVariant $variant, int $quantity = 0): InventoryItem
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Initial stock cannot be negative.');
        }

        return DB::transaction(function () use ($variant, $quantity): InventoryItem {
            $item = InventoryItem::firstOrCreate(
                ['product_variant_id' => $variant->id],
                ['quantity_on_hand' => 0],
            );

            $item = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);

            if ($quantity !== 0) {
                $this->record($item, $variant, $quantity, InventoryMovementType::Initial, null);
            }

            return $item->fresh();
        });
    }

    public function adjust(
        ProductVariant $variant,
        int $delta,
        string $type = 'adjustment',
        ?string $note = null,
    ): InventoryItem {
        $movementType = InventoryMovementType::tryFrom($type);

        if ($movementType === null || ! in_array($movementType, [
            InventoryMovementType::Restock,
            InventoryMovementType::Adjustment,
            InventoryMovementType::Damage,
            InventoryMovementType::Return,
            InventoryMovementType::Correction,
        ], true)) {
            throw new InvalidArgumentException('Invalid inventory adjustment type.');
        }

        return DB::transaction(function () use ($variant, $delta, $movementType, $note): InventoryItem {
            $item = InventoryItem::firstOrCreate(['product_variant_id' => $variant->id]);

            return $this->record($item, $variant, $delta, $movementType, $note);
        });
    }

    public function reserve(ProductVariant $variant, int $quantity): InventoryItem
    {
        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($variant, $quantity): InventoryItem {
            $item = InventoryItem::firstOrCreate(['product_variant_id' => $variant->id]);
            $item = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);

            if ($item->track_quantity && $item->availableQuantity() < $quantity && ! $item->allow_backorders) {
                throw new InvalidArgumentException('Insufficient stock.');
            }

            $before = (int) $item->quantity_reserved;
            $item->quantity_reserved = $before + $quantity;
            $item->save();

            $this->movement(
                $item,
                $variant,
                $quantity,
                $before,
                $item->quantity_reserved,
                InventoryMovementType::Reservation,
            );

            return $item->fresh();
        });
    }

    public function release(ProductVariant $variant, int $quantity): InventoryItem
    {
        $this->ensurePositiveQuantity($quantity);

        return DB::transaction(function () use ($variant, $quantity): InventoryItem {
            $item = InventoryItem::query()
                ->lockForUpdate()
                ->whereBelongsTo($variant, 'variant')
                ->firstOrFail();

            $before = (int) $item->quantity_reserved;
            $after = max(0, $before - $quantity);
            $item->quantity_reserved = $after;
            $item->save();

            $this->movement(
                $item,
                $variant,
                $after - $before,
                $before,
                $after,
                InventoryMovementType::Release,
            );

            return $item->fresh();
        });
    }

    private function record(
        InventoryItem $item,
        ProductVariant $variant,
        int $delta,
        InventoryMovementType $type,
        ?string $note,
    ): InventoryItem {
        $item = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);
        $before = (int) $item->quantity_on_hand;
        $after = $before + $delta;

        if ($after < 0 && ! $item->allow_backorders) {
            throw new InvalidArgumentException('Stock cannot become negative.');
        }

        $item->quantity_on_hand = $after;
        $item->save();

        $this->movement($item, $variant, $delta, $before, $after, $type, $note);

        return $item->fresh();
    }

    private function movement(
        InventoryItem $item,
        ProductVariant $variant,
        int $delta,
        int $before,
        int $after,
        InventoryMovementType $type,
        ?string $note = null,
    ): void {
        $item->movements()->create([
            'product_variant_id' => $variant->id,
            'type' => $type->value,
            'quantity_delta' => $delta,
            'quantity_before' => $before,
            'quantity_after' => $after,
            'note' => $note,
            'created_by' => auth()->id(),
        ]);
    }

    private function ensurePositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Quantity must be greater than zero.');
        }
    }
}
