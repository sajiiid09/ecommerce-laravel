<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->create('pages', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('parent_id')->nullable()->index();
            $t->string('title');
            $t->string('slug')->unique();
            $t->string('page_type')->default('standard');
            $t->string('template')->default('default');
            $t->text('excerpt')->nullable();
            $t->json('content_json')->nullable();
            $t->longText('content_html')->nullable();
            $t->string('status')->default('draft')->index();
            $t->string('visibility')->default('public');
            $t->boolean('show_in_navigation')->default(false);
            $t->boolean('is_indexable')->default(true);
            $t->boolean('open_graph_enabled')->default(true);
            $t->foreignId('featured_media_id')->nullable();
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->string('canonical_url')->nullable();
            $t->foreignId('author_id')->nullable();
            $t->foreignId('updated_by')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamp('scheduled_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $this->create('page_revisions', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('page_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('revision_number');
            $t->json('snapshot');
            $t->string('status')->default('draft');
            $t->foreignId('created_by')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->unique(['page_id', 'revision_number']);
        });
        $this->create('homepage_sections', function (Blueprint $t): void {
            $t->id();
            $t->string('section_key')->unique();
            $t->string('type');
            $t->string('title')->nullable();
            $t->string('eyebrow')->nullable();
            $t->text('subtitle')->nullable();
            $t->boolean('enabled')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->json('settings')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->foreignId('updated_by')->nullable();
            $t->timestamps();
        });
        $this->create('homepage_revisions', function (Blueprint $t): void {
            $t->id();
            $t->unsignedInteger('version');
            $t->json('snapshot');
            $t->string('status')->default('draft');
            $t->foreignId('created_by')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->unique('version');
        });
        $this->create('banners', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('placement')->default('homepage');
            $t->string('eyebrow')->nullable();
            $t->string('title')->nullable();
            $t->text('description')->nullable();
            $t->string('cta_label')->nullable();
            $t->string('destination_type')->nullable();
            $t->string('destination_value')->nullable();
            $t->foreignId('desktop_media_id')->nullable();
            $t->foreignId('mobile_media_id')->nullable();
            $t->string('status')->default('draft')->index();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->json('settings')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->foreignId('updated_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $this->create('menus', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('key')->unique();
            $t->string('location')->nullable()->index();
            $t->boolean('enabled')->default(true);
            $t->timestamps();
        });
        $this->create('menu_items', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->nullable()->index();
            $t->string('label');
            $t->string('type')->default('custom_url');
            $t->string('url')->nullable();
            $t->string('route_name')->nullable();
            $t->boolean('enabled')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->json('settings')->nullable();
            $t->timestamps();
        });
        $this->create('menu_item_targets', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $t->string('target_type');
            $t->unsignedBigInteger('target_id');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['target_type', 'target_id']);
        });
        $this->create('site_settings', function (Blueprint $t): void {
            $t->id();
            $t->string('group');
            $t->string('key');
            $t->json('value')->nullable();
            $t->boolean('is_public')->default(true);
            $t->foreignId('updated_by')->nullable();
            $t->timestamps();
            $t->unique(['group', 'key']);
        });
        $this->create('redirects', function (Blueprint $t): void {
            $t->id();
            $t->string('from_path')->unique();
            $t->string('to_url');
            $t->unsignedSmallInteger('status_code')->default(301);
            $t->boolean('enabled')->default(true);
            $t->unsignedBigInteger('hit_count')->default(0);
            $t->timestamp('last_hit_at')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->foreignId('updated_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        $this->create('redirect_hits', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('redirect_id')->constrained()->cascadeOnDelete();
            $t->string('path')->nullable();
            $t->string('ip_hash')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['redirect_id', 'created_at']);
        });
        $this->create('announcements', function (Blueprint $t): void {
            $t->id();
            $t->string('internal_title');
            $t->text('message');
            $t->string('style')->default('info');
            $t->string('placement')->default('top_bar');
            $t->string('link_label')->nullable();
            $t->string('link_url')->nullable();
            $t->boolean('dismissible')->default(true);
            $t->string('priority')->default('normal');
            $t->string('status')->default('draft')->index();
            $t->timestamp('starts_at')->nullable();
            $t->timestamp('ends_at')->nullable();
            $t->foreignId('created_by')->nullable();
            $t->foreignId('updated_by')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
    }

    private function create(string $table, Closure $callback): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, $callback);
        }
    }

    public function down(): void
    {
        foreach (['redirect_hits', 'redirects', 'announcements', 'site_settings', 'menu_item_targets', 'menu_items', 'menus', 'banners', 'homepage_revisions', 'homepage_sections', 'page_revisions', 'pages'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
