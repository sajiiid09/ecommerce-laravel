<?php

namespace App\View\Composers;

use App\Services\AnnouncementService;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StorefrontLayoutComposer
{
    public function __construct(private readonly AnnouncementService $announcements) {}

    public function compose(View $view): void
    {
        $view->with([
            'cartItems' => StorefrontDemoData::cartItems(),
            'categories' => StorefrontDemoData::categories(),
            'trustItems' => StorefrontDemoData::trustItems(),
            'announcements' => Schema::hasTable('announcements') ? $this->announcements->active('top_bar') : collect(),
        ]);
    }
}
