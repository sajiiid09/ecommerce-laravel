<?php

namespace App\Providers;

use App\Models\Announcement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\MediaAsset;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Redirect;
use App\Models\SiteSetting;
use App\Policies\AnnouncementPolicy;
use App\Policies\BannerPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\HomepageSectionPolicy;
use App\Policies\MediaAssetPolicy;
use App\Policies\MenuPolicy;
use App\Policies\PagePolicy;
use App\Policies\RedirectPolicy;
use App\Policies\SiteSettingPolicy;
use App\View\Composers\StorefrontFooterComposer;
use App\View\Composers\StorefrontHeaderComposer;
use App\View\Composers\StorefrontLayoutComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web'])
                ->name('livewire.update');
        });

        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Banner::class, BannerPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(HomepageSection::class, HomepageSectionPolicy::class);
        Gate::policy(MediaAsset::class, MediaAssetPolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(Redirect::class, RedirectPolicy::class);
        Gate::policy(SiteSetting::class, SiteSettingPolicy::class);

        View::composer('components.layouts.app', StorefrontLayoutComposer::class);
        View::composer('components.store.layout.header', StorefrontHeaderComposer::class);
        View::composer('components.store.layout.footer', StorefrontFooterComposer::class);
    }
}
