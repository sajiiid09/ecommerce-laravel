<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService
{
    public function __construct(private readonly ContentCache $cache) {}

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $cached = Cache::remember($this->cache->settings($group, $key), 300, function () use ($group, $key, $default): array {
            return [
                '__storez_cached_setting' => true,
                'value' => SiteSetting::where(['group' => $group, 'key' => $key])->first()?->value ?? $default,
            ];
        });

        return $cached['value'];
    }

    public function set(string $group, string $key, mixed $value, bool $public = true): SiteSetting
    {
        $setting = SiteSetting::firstOrNew(['group' => $group, 'key' => $key]);
        $setting->fill(['value' => $value, 'is_public' => $public]);
        $setting->forceFill(['updated_by' => auth()->id()]);
        $setting->save();
        Cache::forget($this->cache->settings($group, $key));

        return $setting;
    }
}
