<?php

namespace App\Services;

use App\Models\Announcement;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AnnouncementService
{
    public function __construct(private readonly ContentPublishingService $publishing) {}

    public function active(string $placement)
    {
        return Announcement::active()
            ->where('placement', $placement)
            ->orderByRaw("CASE priority WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->latest()
            ->get();
    }

    public function save(array $data, ?Announcement $item = null): Announcement
    {
        return DB::transaction(function () use ($data, $item): Announcement {
            $item ??= new Announcement;
            $previousPlacement = $item->placement;

            if (! empty($data['starts_at']) && ! empty($data['ends_at']) && $data['ends_at'] < $data['starts_at']) {
                throw new InvalidArgumentException('Announcement end time must be after its start time.');
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
}
