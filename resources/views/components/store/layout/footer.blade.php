@props(['categories' => [], 'footerMenus' => [], 'footerLogo' => null, 'footerSupportEmail' => '', 'footerSocialLinks' => [], 'footerShowNewsletter' => true, 'footerShowPayments' => true, 'footerVisible' => true])

@if($footerVisible)
    <footer class="bg-store-navy text-slate-200">
        <x-store.ui.container class="py-10">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    @if($footerLogo)<img src="{{ $footerLogo }}" alt="StoreZ" class="h-9 w-auto object-contain">@else<h2 class="text-base font-bold text-white">About StoreZ</h2>@endif
                    <p class="mt-3 text-sm leading-6 text-blue-100">{{ $footerDescription }}</p>
                    @if($footerSupportEmail)<a href="mailto:{{ $footerSupportEmail }}" class="mt-3 inline-block text-sm text-blue-100 hover:text-white">{{ $footerSupportEmail }}</a>@endif
                </div>

                @foreach(['shop' => 'Shop', 'help' => 'Customer Service', 'company' => 'Company', 'legal' => 'Legal'] as $menuKey => $menuTitle)
                    @if(count($footerMenus[$menuKey] ?? []))
                        <div><h2 class="text-base font-bold text-white">{{ $menuTitle }}</h2><ul class="mt-3 space-y-2 text-sm">@foreach($footerMenus[$menuKey] as $item)@if($item['enabled'])<li><a href="{{ $item['url'] }}" wire:navigate class="hover:text-white">{{ $item['label'] }}</a></li>@endif @endforeach</ul></div>
                    @endif
                @endforeach

                @if(collect($footerMenus)->flatten(1)->isEmpty())
                    <div><h2 class="text-base font-bold text-white">Popular Categories</h2><ul class="mt-3 space-y-2 text-sm">@foreach(array_slice($categories, 0, 4) as $category)<li><a href="{{ route('store.category', ['slug' => $category['slug']]) }}" wire:navigate class="hover:text-white">{{ $category['name'] }}</a></li>@endforeach</ul></div>
                @endif

                @if($footerShowNewsletter)
                    <div><h2 class="text-base font-bold text-white">Stay in the loop</h2><p class="mt-3 text-sm leading-6">Get offers and product updates in your inbox.</p><form class="mt-4 flex" action="#newsletter"><label for="newsletter-email" class="sr-only">Email address</label><input id="newsletter-email" type="email" placeholder="Your email" class="min-w-0 flex-1 rounded-l-control border-0 bg-white px-3 py-2 text-sm text-store-ink placeholder:text-store-muted outline-none focus:ring-2 focus:ring-store-blue"><button class="rounded-r-control bg-store-blue px-4 text-sm font-semibold text-white hover:bg-store-blue-dark">Subscribe</button></form></div>
                @endif
            </div>

            @if(collect($footerSocialLinks)->filter()->isNotEmpty())
                <div class="mt-8 flex flex-wrap gap-3 border-t border-white/10 pt-5 text-sm">@foreach($footerSocialLinks as $network => $url)@if(filled($url))<a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="rounded-control border border-white/15 px-3 py-2 text-blue-100 hover:text-white">{{ ucfirst($network) }}</a>@endif @endforeach</div>
            @endif

            <div class="mt-10 flex flex-col gap-3 border-t border-white/15 pt-5 text-xs sm:flex-row sm:items-center sm:justify-between"><p>{{ $footerCopyright }}</p>@if($footerShowPayments)<span class="font-semibold text-white">Visa · Mastercard · bKash · Nagad · COD</span>@endif</div>
        </x-store.ui.container>
    </footer>
@endif
