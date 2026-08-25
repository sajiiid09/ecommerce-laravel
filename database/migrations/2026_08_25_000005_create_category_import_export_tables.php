<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_imports', function (Blueprint $table): void {
            $table->id();
            $table->string('filename');
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mode')->default('create');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->string('status')->default('uploaded')->index();
            $table->json('validation_summary')->nullable();
            $table->foreignId('uploaded_by')->nullable();
            $table->foreignId('imported_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('category_exports', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('categories');
            $table->string('format')->default('csv');
            $table->string('scope')->default('all');
            $table->json('fields');
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

    public function down(): void
    {
        Schema::dropIfExists('category_exports');
        Schema::dropIfExists('category_imports');
    }
};
