<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('categories'))
            Schema::create('categories', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('image_url')->nullable();
                $table->foreignId('parent_id')->nullable()->index();
                $table->unsignedBigInteger('media_asset_id')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->unsignedInteger('sort_order')->default(0);
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        if (Schema::hasTable('categories'))
            $this->extend('categories', function (Blueprint $table): void {
                $table->foreignId('parent_id')->nullable()->index();
                $table->unsignedBigInteger('media_asset_id')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->softDeletes();
            }, ['parent_id', 'media_asset_id', 'created_by', 'updated_by', 'deleted_at']);

        $this->create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('logo_media_id')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $this->create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('product')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();
        });

        $this->create('attributes', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('text');
            $table->string('unit')->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        $this->create('attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['attribute_id', 'slug']);
        });

        if (!Schema::hasTable('products'))
            Schema::create('products', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('brand_id')->nullable()->index();
                $table->foreignId('primary_category_id')->nullable()->index();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('product_type')->default('simple')->index();
                $table->text('short_description')->nullable();
                $table->json('description_json')->nullable();
                $table->text('description_html')->nullable();
                $table->string('status')->default('draft')->index();
                $table->string('visibility')->default('visible');
                $table->boolean('is_featured')->default(false);
                $table->boolean('taxable')->default(true);
                $table->string('tax_class')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('canonical_url')->nullable();
                $table->boolean('is_indexable')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        if (Schema::hasTable('products'))
            $this->extend('products', function (Blueprint $table): void {
                $table->foreignId('brand_id')->nullable()->index();
                $table->foreignId('primary_category_id')->nullable()->index();
                $table->string('product_type')->default('simple')->index();
                $table->text('short_description')->nullable();
                $table->json('description_json')->nullable();
                $table->text('description_html')->nullable();
                $table->string('status')->default('draft')->index();
                $table->string('visibility')->default('visible');
                $table->string('tax_class')->nullable();
                $table->boolean('taxable')->default(true);
                $table->boolean('is_indexable')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('canonical_url')->nullable();
                $table->foreignId('created_by')->nullable();
                $table->foreignId('updated_by')->nullable();
                $table->softDeletes();
            }, ['brand_id', 'primary_category_id', 'product_type', 'short_description', 'description_json', 'description_html', 'status', 'visibility', 'tax_class', 'taxable', 'is_indexable', 'published_at', 'meta_title', 'meta_description', 'canonical_url', 'created_by', 'updated_by', 'deleted_at']);

        $this->create('category_product', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'category_id']);
        });
        $this->create('product_tag', function (Blueprint $table): void {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['product_id', 'tag_id']);
        });
        $this->create('product_attribute_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->nullable()->constrained()->nullOnDelete();
            $table->text('text_value')->nullable();
            $table->decimal('number_value', 18, 4)->nullable();
            $table->boolean('boolean_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (!Schema::hasTable('product_options'))
            Schema::create('product_options', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['product_id', 'slug']); });
        if (Schema::hasTable('product_options'))
            $this->extend('product_options', function (Blueprint $table): void {
                $table->string('slug')->nullable()->index(); }, ['slug']);
        if (!Schema::hasTable('product_option_values'))
            Schema::create('product_option_values', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_option_id')->constrained()->cascadeOnDelete();
                $table->string('value');
                $table->string('slug');
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('metadata')->nullable();
                $table->timestamps(); });
        if (Schema::hasTable('product_option_values'))
            $this->extend('product_option_values', function (Blueprint $table): void {
                $table->string('slug')->nullable()->index();
                $table->json('metadata')->nullable(); }, ['slug', 'metadata']);
        if (!Schema::hasTable('product_variants'))
            Schema::create('product_variants', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->string('sku')->unique();
                $table->string('barcode')->nullable();
                $table->string('name')->nullable();
                $table->string('combination_key')->default('default');
                $table->bigInteger('regular_price_minor')->default(0);
                $table->bigInteger('sale_price_minor')->nullable();
                $table->bigInteger('compare_at_price_minor')->nullable();
                $table->bigInteger('cost_price_minor')->nullable();
                $table->unsignedInteger('weight_grams')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['product_id', 'combination_key']); });
        if (Schema::hasTable('product_variants'))
            $this->extend('product_variants', function (Blueprint $table): void {
                $table->string('barcode')->nullable()->index();
                $table->string('combination_key')->default('default');
                $table->bigInteger('regular_price_minor')->nullable();
                $table->bigInteger('sale_price_minor')->nullable();
                $table->bigInteger('compare_at_price_minor')->nullable();
                $table->bigInteger('cost_price_minor')->nullable();
                $table->unsignedInteger('weight_grams')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->softDeletes();
            }, ['barcode', 'combination_key', 'regular_price_minor', 'sale_price_minor', 'compare_at_price_minor', 'cost_price_minor', 'weight_grams', 'sort_order', 'deleted_at']);

        $this->create('product_media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('media_asset_id')->nullable();
            $table->string('path')->nullable();
            $table->string('role')->default('gallery');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });
        $this->create('inventory_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_variant_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('quantity_on_hand')->default(0);
            $table->bigInteger('quantity_reserved')->default(0);
            $table->bigInteger('low_stock_threshold')->default(0);
            $table->boolean('track_quantity')->default(true);
            $table->boolean('allow_backorders')->default(false);
            $table->timestamps();
        });
        $this->create('inventory_movements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->bigInteger('quantity_delta');
            $table->bigInteger('quantity_before');
            $table->bigInteger('quantity_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        $this->create('product_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mode')->default('create');
            foreach (['total_rows', 'valid_rows', 'warning_rows', 'error_rows', 'created_count', 'updated_count', 'skipped_count'] as $column)
                $table->unsignedInteger($column)->default(0);
            $table->string('status')->default('uploaded')->index();
            $table->json('validation_summary')->nullable();
            $table->json('mapping')->nullable();
            $table->foreignId('uploaded_by')->nullable();
            $table->foreignId('imported_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        $this->create('product_exports', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('products');
            $table->string('format')->default('csv');
            $table->string('scope')->default('all');
            $table->json('filters')->nullable();
            $table->json('fields');
            $table->boolean('include_variants')->default(false);
            $table->unsignedInteger('record_count')->default(0);
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('filename')->nullable();
            $table->string('status')->default('queued')->index();
            $table->foreignId('requested_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    private function create(string $table, \Closure $callback): void
    {
        if (!Schema::hasTable($table))
            Schema::create($table, $callback);
    }

    private function extend(string $table, \Closure $callback, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            Schema::create($table, $callback);
            return;
        }
        $missing = array_values(array_filter($columns, fn(string $column): bool => !Schema::hasColumn($table, $column)));
        // Compatibility support is intentionally conservative: the supplied callback
        // describes a complete extension block, so only run it when the block is absent.
        if ($missing && count($missing) === count($columns))
            Schema::table($table, function (Blueprint $blueprint) use ($callback): void {
                $callback($blueprint); });
    }

    public function down(): void
    {
        foreach (['product_exports', 'product_imports', 'inventory_movements', 'inventory_items', 'product_media', 'product_variant_option_value', 'product_variants', 'product_option_values', 'product_options', 'product_attribute_values', 'product_tag', 'category_product', 'products', 'attribute_values', 'attributes', 'tags', 'brands'] as $table)
            if (Schema::hasTable($table))
                Schema::dropIfExists($table);
    }
};
