<?php

namespace App\Livewire\Pages\Admin\Content;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Page;
use App\Services\HomepageService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    protected HomepageService $homepage;

    public function boot(HomepageService $homepage): void
    {
        $this->homepage = $homepage;
    }

    public function render()
    {
        return view('livewire.pages.admin.content.dashboard', [
            'stats' => [
                'Pages' => Page::count(),
                'Published Pages' => Page::published()->count(),
                'Banners' => Banner::count(),
                'Announcements' => Announcement::active()->where('placement', 'storefront')->count(),
                'Homepage Sections' => count($this->homepage->sections(false)),
            ],
            'recentPages' => Page::query()->latest('updated_at')->limit(6)->get(),
            'recentBanners' => Banner::query()->latest('updated_at')->limit(4)->get(),
            'activeAnnouncements' => Announcement::query()->latest('updated_at')->limit(4)->get(),
        ]);
    }
}
