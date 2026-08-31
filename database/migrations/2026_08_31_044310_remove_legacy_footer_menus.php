<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('site_settings')
            ->where('group', 'footer')
            ->whereIn('key', ['shop_menu_key', 'help_menu_key', 'company_menu_key', 'legal_menu_key'])
            ->delete();

        DB::table('menus')
            ->whereIn('key', ['footer-shop', 'footer-help', 'footer-company', 'footer-legal'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
