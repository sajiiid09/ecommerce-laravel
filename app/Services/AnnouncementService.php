<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnnouncementService
{
    public function __construct(
        private readonly ContentPublishingService $publishing,
        private readonly ContentCache $cache,
    ) {}

    public function active(string $placement): Collection
    {
        $cacheKey = $this->cache->announcements($placement);
        $cached = Cache::get($cacheKey);

        if ($cached instanceof Collection && $cached->every(fn (mixed $announcement): bool => $announcement instanceof Announcement)) {
            return $cached;
        }

        if ($cached !== null) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 300, fn (): Collection => Announcement::active()
            ->where('placement', $placement)
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->latest()
            ->get());
    }

    public function save(array $data, ?Announcement $item = null): Announcement
    {
        return DB::transaction(function () use ($data, $item): Announcement {
            $item ??= new Announcement;
            $previousPlacement = $item->placement;

            if (! empty($data['starts_at']) && ! empty($data['ends_at']) && $data['ends_at'] < $data['starts_at']) {
                throw new InvalidArgumentException('Announcement end time must be after its start time.');
            }

            if (($data['status'] ?? $item->status) === 'scheduled' && empty($data['starts_at'])) {
                throw new InvalidArgumentException('Scheduled announcements require a start time.');
            }

            $item->fill($data);
            $item->forceFill([
                'created_by' => $item->created_by ?? auth()->id(),
                'updated_by' => auth()->id(),
            ]);
            $item->save();

            if ($previousPlacement) {
                $this->publishing->invalidate('announcement', $previousPlacement);
            }

            $this->publishing->invalidate('announcement', $item->placement);

            return $item;
        });
    }

    public function delete(Announcement $item): void
    {
        $placement = $item->placement;
        $item->forceDelete();
        $this->publishing->invalidate('announcement', $placement);
    }
}
