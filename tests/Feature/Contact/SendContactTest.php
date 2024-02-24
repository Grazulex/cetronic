<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

it('can see contact page', function (): void {
    $this->get(uri: route('contact'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee(__('contact.title'))
        ->assertSee(__('contact.form.firstname'))
        ->assertSee(__('contact.form.email'))
        ->assertSee(__('contact.form.lastname'))
        ->assertSee(__('contact.form.phone'))
        ->assertSee(__('contact.form.message'))
        ->assertSee(__('contact.form.submit'));
});

it('can send a contact', function (): void {
    $response = $this->post(
        uri: route('contact.send'),
        data: [
            'firstname' => fake()->firstName,
            'lastname' => fake()->lastName,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'message' => fake()->text,
        ]
    );
    $response->assertStatus(Response::HTTP_FOUND);
    $response->assertRedirect(route('contact.thanks'));
    $this->followRedirects($response)->assertSee(__('contact.thanks'));
});

it('can not send a contact if wrong data', function (): void {
    $this->post(
        uri: route('contact.send'),
        data: [
            'firstname' => fake()->firstName,
            'lastname' => fake()->lastName,
            'email' => '',
            'phone' => fake()->phoneNumber,
            'message' => fake()->text,
        ]
    )->assertSessionHasErrors('email');
});
