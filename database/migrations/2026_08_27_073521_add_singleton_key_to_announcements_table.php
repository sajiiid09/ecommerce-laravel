<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $newestAnnouncementId = DB::table('announcements')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('id');

        if ($newestAnnouncementId !== null) {
            DB::table('announcements')
                ->whereNull('deleted_at')
                ->where('id', '!=', $newestAnnouncementId)
                ->delete();
        }

        DB::table('announcements')->whereNotNull('deleted_at')->delete();

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('singleton_key')->default('default');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->unique('singleton_key', 'announcements_singleton_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropUnique('announcements_singleton_key_unique');
            $table->dropColumn('singleton_key');
        });
    }
};
