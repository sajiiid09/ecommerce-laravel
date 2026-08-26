<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1280px]">
        <x-admin.cms.page-header eyebrow="StoreZ / Content / Footer" title="Footer settings" description="Manage footer messaging, navigation, social links, and visibility across the storefront.">
            <x-slot:actions>
                <button wire:click="saveFooter" wire:loading.attr="disabled" wire:target="saveFooter" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm disabled:opacity-60">
                    <x-ui.icon name="check" class="size-4" /><span wire:loading.remove wire:target="saveFooter">Save footer</span><span wire:loading wire:target="saveFooter">Saving...</span>
                </button>
            </x-slot:actions>
        </x-admin.cms.page-header>
        @if (session('status'))<div class="mb-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-5">
                <x-admin.cms.panel title="Footer content" description="Keep contact and legal information current for every storefront page.">
                    <div class="space-y-4">
                        <label class="block text-sm font-semibold text-slate-700">Description<textarea wire:model.live="description" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="Tell customers about your store"></textarea></label>
                        <label class="block text-sm font-semibold text-slate-700">Support email<input type="email" wire:model.live="support_email" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label>
                        <label class="block text-sm font-semibold text-slate-700">WhatsApp number<input type="tel" wire:model.live="whatsapp_number" autocomplete="tel" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm" placeholder="8801XXXXXXXXX"><span class="mt-1 block text-xs font-normal text-slate-500">Use the international number with country code. Leave empty to hide the floating WhatsApp button.</span>@error('whatsapp_number')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror</label>
                        <label class="block text-sm font-semibold text-slate-700">Copyright<input wire:model.live="copyright" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm"></label>
                    </div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Footer branding and visibility">
                    <div class="space-y-4">
                        <x-admin.media-picker :assets="$mediaAssets" :selected="$logo_media_id" title="Footer logo" context="footer_logo" />
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Show footer</span><input type="checkbox" wire:model.live="show_footer" class="rounded border-slate-300 text-blue-600"></label>
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Show newsletter</span><input type="checkbox" wire:model.live="show_newsletter" class="rounded border-slate-300 text-blue-600"></label>
                        <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3 text-sm font-semibold"><span>Show payment methods</span><input type="checkbox" wire:model.live="show_payment_methods" class="rounded border-slate-300 text-blue-600"></label>
                    </div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Footer menus" description="Choose the existing Navigation Manager menus for each column.">
                    <div class="grid gap-3 sm:grid-cols-2">@foreach(['shop_menu_key' => 'Shop', 'help_menu_key' => 'Help', 'company_menu_key' => 'Company', 'legal_menu_key' => 'Legal'] as $field => $label)<label class="block text-sm font-semibold text-slate-700">{{ $label }} menu<select wire:model.live="{{ $field }}" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm"><option value="">None</option>@foreach($menus as $menu)<option wire:key="footer-{{ $field }}-{{ $menu->key }}" value="{{ $menu->key }}">{{ $menu->name }}</option>@endforeach</select></label>@endforeach</div>
                </x-admin.cms.panel>
                <x-admin.cms.panel title="Social links" description="Store a JSON object such as {&quot;facebook&quot;:&quot;https://...&quot;,&quot;instagram&quot;:&quot;https://...&quot;}.">
                    <textarea wire:model.live="social_links_json" rows="6" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 font-mono text-xs" spellcheck="false"></textarea>
                </x-admin.cms.panel>
            </div>
            <x-admin.cms.panel title="Footer preview" description="The saved settings rendered by the storefront footer component.">
                <iframe src="{{ route('admin.content.footer.preview') }}" title="Storefront footer preview" class="h-72 w-full rounded-xl border border-slate-200 bg-white"></iframe>
                <p class="mt-3 text-xs text-slate-500">Save changes, then refresh this preview to verify the complete footer.</p>
            </x-admin.cms.panel>
        </div>
    </div>
</div>
