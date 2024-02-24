<?php

declare(strict_types=1);

use App\Listeners\UserLoginAt;
use Illuminate\Auth\Events\Login;

it('can go to login page', function (): void {
    $this->get(uri: route('login'))
        ->assertStatus(200)
        ->assertSee(__('user.login'))
        ->assertSee(__('user.email'))
        ->assertSee(__('user.password'));
    ;
});

it('can not login if wrong data', function (): void {
    $this->post(
        uri: '/login',
        data: [
            'email' => fake()->email,
            'password' => 'password',
        ]
    )
        ->assertSessionHasErrors('email');
});

it('can login', function (): void {
    $user = loginAsUser();
    $this->post(
        uri: '/login',
        data: [
            'email' => $user->email,
            'password' => 'password',
        ]
    )
        ->assertRedirect(route('home'));
});


it('has event or update updated_at after login', function (): void {
    $user = loginAsUser();
    $this->post(
        uri: '/login',
        data: [
            'email' => $user->email,
            'password' => 'password',
        ]
    )
        ->assertRedirect(route('home'));

    Event::fake();
    Event::assertListening(
        Login::class,
        UserLoginAt::class,
    );
});

it('has update updated_at after login', function (): void {
    Event::fake();
    $user = loginAsUser();
    $event = new Login(auth()->guard(), $user, false);
    $listener = new UserLoginAt();
    $listener->handle($event);

    expect($user->logged_at)->not()->toBeNull();
});

it('can not access user dashboard', function (): void {
    $this->get(uri: route('user_dashboard'))
        ->assertRedirect(route('login'));
});

it('can  access user dashboard', function (): void {
    loginAsUser();
    $this->get(uri: route('user_dashboard'))
        ->assertSee(__('user.dashboard'));
});
