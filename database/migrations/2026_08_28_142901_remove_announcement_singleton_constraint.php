<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements') || ! Schema::hasColumn('announcements', 'singleton_key')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropUnique('announcements_singleton_key_unique');
            $table->dropColumn('singleton_key');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('announcements') || Schema::hasColumn('announcements', 'singleton_key')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table): void {
            $table->string('singleton_key')->default('default');
        });
    }
};
