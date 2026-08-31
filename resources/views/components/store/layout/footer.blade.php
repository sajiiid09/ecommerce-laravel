@props(['categories' => [], 'footerColumns' => [], 'footerLogo' => null, 'footerStoreName' => 'StoreZ', 'footerStoreTagline' => '', 'footerSupportEmail' => '', 'footerSupportPhone' => '', 'footerAddress' => '', 'footerSocialLinks' => [], 'footerShowNewsletter' => true, 'footerShowPayments' => true, 'footerVisible' => true])

@if($footerVisible)
    <footer class="bg-store-navy text-slate-200">
        <x-store.ui.container class="py-10">
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    @if($footerLogo)<img src="{{ $footerLogo }}" alt="{{ $footerStoreName }}" loading="lazy" decoding="async" class="h-9 w-auto object-contain">@else<h2 class="text-base font-bold text-white">About {{ $footerStoreName }}</h2>@endif
                    @if($footerStoreTagline)<p class="mt-2 text-xs font-semibold text-blue-100">{{ $footerStoreTagline }}</p>@endif
                    <p class="mt-3 text-sm leading-6 text-blue-100">{{ $footerDescription }}</p>
                    @if($footerSupportEmail)<a href="mailto:{{ $footerSupportEmail }}" class="mt-3 inline-block text-sm text-blue-100 hover:text-white">{{ $footerSupportEmail }}</a>@endif
                    @if($footerSupportPhone)<a href="tel:{{ $footerSupportPhone }}" class="mt-1 block text-sm text-blue-100 hover:text-white">{{ $footerSupportPhone }}</a>@endif
                    @if($footerAddress)<p class="mt-1 text-sm text-blue-100">{{ $footerAddress }}</p>@endif
                </div>

                @foreach($footerColumns as $column)
                    @if($column['enabled'])
                        <div><h2 class="text-base font-bold text-white">{{ $column['title'] }}</h2><ul class="mt-3 space-y-2 text-sm">@foreach($column['links'] as $link)<li><a href="{{ $link['url'] }}" @if(str_starts_with($link['url'], url('/'))) wire:navigate @endif class="hover:text-white">{{ $link['name'] }}</a></li>@endforeach</ul></div>
                    @endif
                @endforeach

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
