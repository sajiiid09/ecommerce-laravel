<?php

use App\Livewire\Pages\Admin\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows the administrator menu and protects the profile page', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Profile')
        ->assertSee(route('admin.profile'))
        ->assertSee('Sign out')
        ->assertSee('!inline-flex !flex-row !items-center', false)
        ->assertSee('col-span-full border-b border-neutral-200', false)
        ->assertSee('<form method="POST" action="'.route('logout').'" class="col-span-full">', false)
        ->assertSee('action="'.route('logout').'"', false);

    $this->actingAs($admin)
        ->get(route('admin.profile'))
        ->assertOk()
        ->assertSee('Admin profile');

    $this->actingAs($customer)
        ->get(route('admin.profile'))
        ->assertForbidden();
});

it('updates the authenticated administrator profile', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email' => 'admin@example.test',
    ]);

    Livewire::actingAs($admin)
        ->test(Profile::class)
        ->set('name', 'Updated Administrator')
        ->set('email', 'updated-admin@example.test')
        ->set('phone', '+8801700000000')
        ->call('save')
        ->assertHasNoErrors();

    expect($admin->fresh()->only(['name', 'email', 'phone']))
        ->toBe([
            'name' => 'Updated Administrator',
            'email' => 'updated-admin@example.test',
            'phone' => '+8801700000000',
        ]);
});

it('validates duplicate administrator email addresses', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $otherUser = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(Profile::class)
        ->set('email', $otherUser->email)
        ->call('save')
        ->assertHasErrors(['email']);
});
