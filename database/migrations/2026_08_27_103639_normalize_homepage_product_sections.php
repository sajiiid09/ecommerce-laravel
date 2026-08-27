<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('homepage_sections')) {
            return;
        }

        $sources = [
            'featured_products' => 'featured',
            'new_arrivals' => 'newest',
            'bestsellers' => 'bestsellers',
            'flash_deals' => 'on_sale',
        ];

        DB::table('homepage_sections')
            ->whereIn('type', array_keys($sources))
            ->orderBy('id')
            ->chunkById(100, function ($sections) use ($sources): void {
                foreach ($sections as $section) {
                    $settings = is_string($section->settings)
                        ? json_decode($section->settings, true)
                        : (array) $section->settings;
                    $settings = is_array($settings) ? $settings : [];
                    $settings['source'] ??= $sources[$section->type];
                    $settings['sort'] ??= 'default';
                    $settings['limit'] = max(1, min(24, (int) ($settings['limit'] ?? 6)));

                    DB::table('homepage_sections')
                        ->where('id', $section->id)
                        ->update([
                            'type' => 'products',
                            'settings' => json_encode($settings),
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('homepage_sections')) {
            return;
        }

        $types = [
            'featured' => 'featured_products',
            'newest' => 'new_arrivals',
            'bestsellers' => 'bestsellers',
            'on_sale' => 'flash_deals',
        ];

        DB::table('homepage_sections')
            ->where('type', 'products')
            ->orderBy('id')
            ->chunkById(100, function ($sections) use ($types): void {
                foreach ($sections as $section) {
                    $settings = is_string($section->settings)
                        ? json_decode($section->settings, true)
                        : (array) $section->settings;
                    $settings = is_array($settings) ? $settings : [];
                    $source = $settings['source'] ?? null;

                    if (! isset($types[$source])) {
                        continue;
                    }

                    unset($settings['source'], $settings['sort']);
                    DB::table('homepage_sections')
                        ->where('id', $section->id)
                        ->update([
                            'type' => $types[$source],
                            'settings' => json_encode($settings),
                            'updated_at' => now(),
                        ]);
                }
            });
    }
};
