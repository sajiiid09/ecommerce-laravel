<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('product_variant_option_value')) {
            Schema::create('product_variant_option_value', function (Blueprint $t) {
                $t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
                $t->foreignId('product_option_value_id')->constrained()->cascadeOnDelete();
                $t->unique(['product_variant_id', 'product_option_value_id'], 'pvo_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variant_option_value');
    }
};
