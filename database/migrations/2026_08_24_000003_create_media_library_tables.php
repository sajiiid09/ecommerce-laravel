<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
  public function up(): void
  {
    if (!Schema::hasTable('media_folders'))
      Schema::create('media_folders', function (Blueprint $t) {
        $t->id();
        $t->foreignId('parent_id')->nullable()->constrained('media_folders')->nullOnDelete();
        $t->string('name');
        $t->string('slug');
        $t->timestamps();
        $t->unique(['parent_id', 'slug']); });
    if (!Schema::hasTable('media_assets'))
      Schema::create('media_assets', function (Blueprint $t) {
        $t->id();
        $t->foreignId('folder_id')->nullable()->constrained('media_folders')->nullOnDelete();
        $t->string('disk')->default('public');
        $t->string('path');
        $t->string('filename');
        $t->string('mime_type')->nullable();
        $t->unsignedBigInteger('size')->nullable();
        $t->unsignedInteger('width')->nullable();
        $t->unsignedInteger('height')->nullable();
        $t->foreignId('uploaded_by')->nullable();
        $t->timestamps();
        $t->unique(['disk', 'path']); });
    if (!Schema::hasTable('media_usages'))
      Schema::create('media_usages', function (Blueprint $t) {
        $t->id();
        $t->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
        $t->string('usable_type');
        $t->unsignedBigInteger('usable_id');
        $t->string('role')->nullable();
        $t->timestamps();
        $t->index(['usable_type', 'usable_id']); }); }
  public function down(): void
  {
    Schema::dropIfExists('media_usages');
    Schema::dropIfExists('media_assets');
    Schema::dropIfExists('media_folders'); }
};
