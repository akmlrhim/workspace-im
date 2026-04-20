<?php

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeGoogleUser(array $overrides = []): SocialiteUser
{
    $raw = array_merge([
        'sub' => '123456789012345678901',
        'email' => 'person@example.com',
        'email_verified' => true,
        'name' => 'Jane Doe',
        'picture' => 'https://lh3.googleusercontent.com/a/default',
    ], $overrides);

    $user = new SocialiteUser;
    $user->setRaw($raw)->map([
        'id' => $raw['sub'],
        'name' => $raw['name'] ?? null,
        'email' => $raw['email'] ?? null,
        'avatar' => $raw['picture'] ?? null,
    ]);

    return $user;
}

function mockGoogleSocialite(SocialiteUser $user): void
{
    $provider = Mockery::mock(GoogleProvider::class);
    $provider->shouldReceive('user')->andReturn($user);
    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}

test('google login rejected when email not verified by google', function () {
    mockGoogleSocialite(fakeGoogleUser(['email_verified' => false]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('google login does NOT match by email to prevent account takeover', function () {
    $victim = User::factory()->create([
        'email' => 'victim@example.com',
        'role' => 'administrator',
        'google_id' => null,
    ]);

    mockGoogleSocialite(fakeGoogleUser([
        'sub' => 'attacker-google-id',
        'email' => 'victim@example.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    $this->assertGuest();

    $victim->refresh();
    expect($victim->google_id)->toBeNull();
    expect($victim->role)->toBe('administrator');
});

test('google login creates new guest user when email is unknown', function () {
    mockGoogleSocialite(fakeGoogleUser([
        'sub' => 'new-user-id',
        'email' => 'newcomer@example.com',
        'name' => 'Newcomer',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect();

    $user = User::where('email', 'newcomer@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->google_id)->toBe('new-user-id');
    expect($user->role)->toBe('guest');
    $this->assertAuthenticatedAs($user);
});

test('google login signs in existing user matched by google_id', function () {
    $user = User::factory()->withGoogle()->create([
        'google_id' => 'existing-gid',
        'email' => 'existing@example.com',
    ]);

    mockGoogleSocialite(fakeGoogleUser([
        'sub' => 'existing-gid',
        'email' => 'existing@example.com',
    ]));

    $this->get(route('auth.google.callback'))
        ->assertRedirect();

    $this->assertAuthenticatedAs($user->fresh());
});

test('google linking rejects if another user already claims the google_id', function () {
    $otherUser = User::factory()->withGoogle()->create(['google_id' => 'shared-gid']);
    $actor = User::factory()->create(['google_id' => null]);

    mockGoogleSocialite(fakeGoogleUser(['sub' => 'shared-gid']));

    $this->actingAs($actor)
        ->withSession(['google.linking_user_id' => $actor->id])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('profile.edit'));

    $actor->refresh();
    expect($actor->google_id)->toBeNull();
    expect($otherUser->fresh()->google_id)->toBe('shared-gid');
});

test('google linking rejects when session user does not match authenticated user', function () {
    $actor = User::factory()->create(['google_id' => null]);
    $otherActor = User::factory()->create(['google_id' => null]);

    mockGoogleSocialite(fakeGoogleUser(['sub' => 'fresh-gid']));

    $this->actingAs($actor)
        ->withSession(['google.linking_user_id' => $otherActor->id])
        ->get(route('auth.google.callback'))
        ->assertRedirect(route('login'));

    expect($actor->fresh()->google_id)->toBeNull();
    expect($otherActor->fresh()->google_id)->toBeNull();
});
