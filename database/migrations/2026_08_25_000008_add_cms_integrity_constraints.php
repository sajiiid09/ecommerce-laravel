<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->failOnOrphans('pages', 'parent_id', 'pages');
        $this->failOnOrphans('pages', 'featured_media_id', 'media_assets');
        $this->failOnOrphans('pages', 'author_id', 'users');
        $this->failOnOrphans('pages', 'updated_by', 'users');
        $this->failOnOrphans('page_revisions', 'created_by', 'users');
        $this->failOnOrphans('homepage_sections', 'created_by', 'users');
        $this->failOnOrphans('homepage_sections', 'updated_by', 'users');
        $this->failOnOrphans('homepage_revisions', 'created_by', 'users');
        $this->failOnOrphans('banners', 'desktop_media_id', 'media_assets');
        $this->failOnOrphans('banners', 'mobile_media_id', 'media_assets');
        $this->failOnOrphans('banners', 'created_by', 'users');
        $this->failOnOrphans('banners', 'updated_by', 'users');
        $this->failOnOrphans('site_settings', 'updated_by', 'users');
        $this->failOnOrphans('redirects', 'created_by', 'users');
        $this->failOnOrphans('redirects', 'updated_by', 'users');
        $this->failOnOrphans('announcements', 'created_by', 'users');
        $this->failOnOrphans('announcements', 'updated_by', 'users');
        $this->failOnOrphans('media_assets', 'uploaded_by', 'users');
        $this->failOnOrphans('media_folders', 'created_by', 'users');
        $this->failOnOrphans('media_folders', 'updated_by', 'users');

        Schema::table('pages', function (Blueprint $table): void {
            $table->foreign('parent_id')->references('id')->on('pages')->nullOnDelete();
            $table->foreign('featured_media_id')->references('id')->on('media_assets')->nullOnDelete();
            $table->foreign('author_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'published_at', 'scheduled_at'], 'pages_publication_lookup_index');
        });

        Schema::table('page_revisions', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('homepage_sections', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['enabled', 'sort_order'], 'homepage_sections_display_index');
        });

        Schema::table('homepage_revisions', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('banners', function (Blueprint $table): void {
            $table->foreign('desktop_media_id')->references('id')->on('media_assets')->nullOnDelete();
            $table->foreign('mobile_media_id')->references('id')->on('media_assets')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['placement', 'status', 'starts_at', 'ends_at'], 'banners_schedule_lookup_index');
        });

        Schema::table('site_settings', function (Blueprint $table): void {
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('redirects', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['enabled', 'from_path'], 'redirects_resolution_index');
        });

        Schema::table('announcements', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['placement', 'status', 'starts_at', 'ends_at'], 'announcements_schedule_lookup_index');
        });

        Schema::table('media_assets', function (Blueprint $table): void {
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('media_folders', function (Blueprint $table): void {
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function failOnOrphans(string $table, string $column, string $referencedTable): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || ! Schema::hasTable($referencedTable)) {
            return;
        }

        $orphan = DB::table($table)
            ->whereNotNull($column)
            ->whereNotExists(fn ($query) => $query
                ->select(DB::raw('1'))
                ->from($referencedTable)
                ->whereColumn($referencedTable.'.id', $table.'.'.$column))
            ->value('id');

        if ($orphan !== null) {
            throw new RuntimeException("Cannot add CMS foreign key {$table}.{$column}: orphaned record {$orphan} references {$referencedTable}.");
        }
    }

    public function down(): void
    {
        // This additive integrity migration is intentionally not reversed automatically.
    }
};
