@if (request()->is('admin*'))
    @include('layouts.admin')
@else
    @include('components.layouts.app')
@endif
