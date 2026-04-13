<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get('/settings/profile')->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});

test('google only user cannot change email', function () {
    $user = User::factory()->googleOnly()->create();

    $this->actingAs($user);

    $originalEmail = $user->email;

    $response = Livewire::test(Profile::class)
        ->set('name', 'New Name')
        ->set('email', 'hacker@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('New Name');
    expect($user->email)->toEqual($originalEmail);
});

test('google only user sees disabled email field', function () {
    $user = User::factory()->googleOnly()->create();

    $this->actingAs($user)
        ->get('/settings/profile')
        ->assertOk()
        ->assertSee('Email dikelola oleh Google');
});

test('regular user with google linked can change email', function () {
    $user = User::factory()->withGoogle()->create();

    $this->actingAs($user);

    Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'newemail@example.com')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->email)->toEqual('newemail@example.com');
});

test('google only user cannot unlink google account', function () {
    $user = User::factory()->googleOnly()->create();

    $this->actingAs($user)
        ->post(route('auth.google.unlink'))
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->google_id)->not->toBeNull();
});

test('regular user with google linked can unlink google account', function () {
    $user = User::factory()->withGoogle()->create();

    $this->actingAs($user)
        ->post(route('auth.google.unlink'))
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->google_id)->toBeNull();
    expect($user->avatar)->toBeNull();
});

test('google only user can delete account by typing HAPUS', function () {
    $user = User::factory()->googleOnly()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('confirmText', 'HAPUS')
        ->call('deleteUser')
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('google only user cannot delete account with wrong confirmation', function () {
    $user = User::factory()->googleOnly()->create();

    $this->actingAs($user);

    Livewire::test('settings.delete-user-form')
        ->set('confirmText', 'wrong')
        ->call('deleteUser')
        ->assertHasErrors(['confirmText']);

    expect($user->fresh())->not->toBeNull();
});
