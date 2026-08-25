<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_folders')) {
            Schema::table('media_folders', function (Blueprint $table): void {
                if (! Schema::hasColumn('media_folders', 'created_by')) {
                    $table->foreignId('created_by')->nullable();
                }
                if (! Schema::hasColumn('media_folders', 'updated_by')) {
                    $table->foreignId('updated_by')->nullable();
                }
            });
        }

        if (Schema::hasTable('media_assets')) {
            Schema::table('media_assets', function (Blueprint $table): void {
                if (! Schema::hasColumn('media_assets', 'original_filename')) {
                    $table->string('original_filename')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'extension')) {
                    $table->string('extension')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'alt_text')) {
                    $table->text('alt_text')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'title')) {
                    $table->string('title')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'caption')) {
                    $table->text('caption')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'checksum')) {
                    $table->string('checksum', 64)->nullable()->index();
                }
                if (! Schema::hasColumn('media_assets', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
                if (! Schema::hasColumn('media_assets', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        // The media upgrade is intentionally additive and is not rolled back automatically.
    }
};
