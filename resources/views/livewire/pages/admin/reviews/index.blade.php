<div class="p-5 sm:p-8">
    <div class="mx-auto max-w-[1480px]">
        <div>
            <p class="text-sm text-slate-500">StoreZ / Reviews</p>
            <h1 class="mt-1 text-3xl font-extrabold tracking-tight text-slate-900">Product Reviews</h1>
            <p class="mt-1 text-sm text-slate-500">Moderate customer feedback before it appears publicly.</p>
        </div>

        <x-admin.cms.panel title="Filter" class="mt-6">
            <x-ui.select wire:model.live="status" placeholder="All reviews" class="w-44">
                <x-ui.select.option value="pending">Pending</x-ui.select.option>
                <x-ui.select.option value="approved">Approved</x-ui.select.option>
                <x-ui.select.option value="rejected">Rejected</x-ui.select.option>
                <x-ui.select.option value="all">All reviews</x-ui.select.option>
            </x-ui.select>
        </x-admin.cms.panel>

        <x-ui.table
            :paginator="$reviews"
            wire:loading
            loadOn="pagination"
            pagination:variant="full"
            :pagination:options="[10, 15, 25, 50]"
            table:class="w-full min-w-[980px] text-left"
            class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        >
            <x-ui.table.header class="bg-slate-50 text-[11px] font-semibold tracking-wider text-slate-400">
                <x-ui.table.columns>
                    <x-ui.table.head>Product</x-ui.table.head>
                    <x-ui.table.head>Customer</x-ui.table.head>
                    <x-ui.table.head>Rating</x-ui.table.head>
                    <x-ui.table.head>Review</x-ui.table.head>
                    <x-ui.table.head>Status</x-ui.table.head>
                    <x-ui.table.head>Action</x-ui.table.head>
                </x-ui.table.columns>
            </x-ui.table.header>
            <x-ui.table.rows class="divide-y divide-slate-100">
                @forelse($reviews as $review)
                    <x-ui.table.row :key="$review->id" class="text-slate-700 hover:bg-slate-50">
                        <x-ui.table.cell class="px-3 py-4 font-bold text-slate-800">
                            {{ $review->product?->name ?? '—' }}
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-3 py-4">
                            <p class="font-semibold text-slate-800">{{ $review->name }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $review->created_at->format('M j, Y') }}</p>
                            @if($review->is_verified_purchase)
                                <p class="mt-1 text-xs font-bold text-emerald-600">Verified purchase</p>
                            @endif
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-3 py-4">
                            <span class="text-amber-500" aria-label="{{ $review->rating }} out of 5 stars">
                                {{ str_repeat('★', $review->rating) }}
                            </span>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="max-w-[360px] whitespace-normal px-3 py-4">
                            @if($review->title)
                                <p class="font-bold text-slate-800">{{ $review->title }}</p>
                            @endif
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $review->review }}</p>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-3 py-4">
                            <x-ui.badge variant="solid" :color="match ($review->status) {
                                'approved' => 'emerald',
                                'rejected' => 'red',
                                default => 'amber',
                            }" pill size="sm">
                                {{ ucfirst($review->status) }}
                            </x-ui.badge>
                        </x-ui.table.cell>
                        <x-ui.table.cell class="px-3 py-4">
                            <x-ui.dropdown position="bottom-end">
                                <x-slot:button>
                                    <x-ui.button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        color="slate"
                                        icon-after="chevron-down"
                                        aria-label="Actions for {{ $review->product?->name ?? 'review' }} by {{ $review->name }}"
                                    >Action</x-ui.button>
                                </x-slot:button>
                                <x-slot:menu>
                                    <x-ui.dropdown.item wire:click="view({{ $review->id }})" icon="eye">
                                        View
                                    </x-ui.dropdown.item>
                                    @if($review->status !== 'approved')
                                        <x-ui.dropdown.item wire:click="approve({{ $review->id }})" icon="check">
                                            Approve
                                        </x-ui.dropdown.item>
                                    @endif
                                    @if($review->status !== 'rejected')
                                        <x-ui.dropdown.item
                                            wire:click="reject({{ $review->id }})"
                                            wire:confirm="Reject this review?"
                                            icon="x-mark"
                                        >
                                            Reject
                                        </x-ui.dropdown.item>
                                    @endif
                                    <x-ui.dropdown.separator />
                                    <x-ui.dropdown.item
                                        wire:click="delete({{ $review->id }})"
                                        wire:confirm="Delete this review?"
                                        variant="danger"
                                    >
                                        Delete
                                    </x-ui.dropdown.item>
                                </x-slot:menu>
                            </x-ui.dropdown>
                        </x-ui.table.cell>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty>No reviews in this state.</x-ui.table.empty>
                @endforelse
            </x-ui.table.rows>
        </x-ui.table>

        <x-ui.modal
            id="review-details"
            width="2xl"
            heading="Review details"
            description="Full customer feedback and moderation details."
        >
            @if($viewingReview)
                <div class="space-y-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-lg font-bold text-slate-900">{{ $viewingReview->product?->name ?? 'Unknown product' }}</p>
                            <p class="mt-1 text-sm text-slate-500">Submitted by {{ $viewingReview->name }} on {{ $viewingReview->created_at->format('M j, Y \\a\\t g:i A') }}</p>
                        </div>
                        <x-ui.badge variant="solid" :color="match ($viewingReview->status) {
                            'approved' => 'emerald',
                            'rejected' => 'red',
                            default => 'amber',
                        }" pill>
                            {{ ucfirst($viewingReview->status) }}
                        </x-ui.badge>
                    </div>

                    <div class="grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Customer</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ $viewingReview->name }}</p>
                            @if($viewingReview->email)
                                <p class="mt-1 text-sm text-slate-600">{{ $viewingReview->email }}</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rating</p>
                            <p class="mt-1 text-amber-500" aria-label="{{ $viewingReview->rating }} out of 5 stars">
                                {{ str_repeat('★', $viewingReview->rating) }}
                            </p>
                            @if($viewingReview->is_verified_purchase)
                                <p class="mt-1 text-xs font-bold text-emerald-600">Verified purchase</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        @if($viewingReview->title)
                            <h3 class="text-base font-bold text-slate-900">{{ $viewingReview->title }}</h3>
                        @endif
                        <p class="{{ $viewingReview->title ? 'mt-2' : '' }} whitespace-pre-line text-sm leading-7 text-slate-700">{{ $viewingReview->review }}</p>
                    </div>
                </div>
            @endif
        </x-ui.modal>
    </div>
</div>
