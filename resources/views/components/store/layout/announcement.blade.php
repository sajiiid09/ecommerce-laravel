@props(['announcement'])
<div x-data="{ dismissed: localStorage.getItem('storez-announcement-{{ $announcement->id }}') === '1' }" x-cloak x-show="!dismissed" class="bg-store-blue px-4 py-2 text-center text-sm text-white">
    <span>{{ $announcement->message }}</span>
    @if($announcement->link_url)<a href="{{ $announcement->link_url }}" class="ml-2 font-bold underline">{{ $announcement->link_label ?: 'Learn more' }}</a>@endif
    @if($announcement->dismissible)<button type="button" @click="dismissed = true; localStorage.setItem('storez-announcement-{{ $announcement->id }}', '1')" class="ml-3 font-bold" aria-label="Dismiss">×</button>@endif
</div>
