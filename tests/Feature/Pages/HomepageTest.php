<?php

declare(strict_types=1);

use App\Models\Brand;
use App\Models\Category;
use App\Models\UserBrand;
use Symfony\Component\HttpFoundation\Response;

it('can see the home page in EN', function (): void {
    $this->get(uri: route('home'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Cetronic Benelux is a company active in the field of watchmaking.');
});

it('can see the home page in FR', function (): void {
    $this->refreshApplicationWithLocale('fr');
    $this->get(uri: route('home'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Cetronic Benelux est une société active dans le domaine de l’horlogerie.');
});

it('can see the home page in NL', function (): void {
    $this->refreshApplicationWithLocale('nl');
    $this->get(uri: route('home'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Cetronic Benelux is een bedrijf actief op het gebied van uurwerken');
});


it('can not see promo if not logged', function (): void {
    createItems(10);
    $response = $this->get(uri: route('home'));
    $response->assertStatus(Response::HTTP_OK);

    expect($response->getContent())->not()->toContain(__('home.items.promo'));
    ;
});

it('can see promo if logged', function (): void {
    createItems(10);
    $user = loginAsUser();
    $response = $this->post(
        uri: '/login',
        data: [
            'email' => $user->email,
            'password' => 'password',
        ],
    );
    $response->assertRedirect(route('home'));
    $this->followRedirects($response)->assertSee(__('home.items.promo'));
});

it('can see menu with category and brand without login', function (): void {
    $items = createItems(15);
    foreach ($items as $item) {
        $this->get(uri: route('home'))
            ->assertStatus(Response::HTTP_OK)
            ->assertSee($item->category->name)
            ->assertSee($item->brand->name);
    }
});

it('can not see a category disable in menu', function (): void {
    createItems(10);
    $user = loginAsUser();
    $brand = Brand::first();
    $category = Category::first();
    UserBrand::factory()->count(1)->create(['user_id' => $user->id, 'brand_id' => $brand->id, 'category_id' => $category->id, 'is_excluded' => true]);

    $response = $this->post(
        uri: '/login',
        data: [
            'email' => $user->email,
            'password' => 'password',
        ],
    );
    $response->assertRedirect(route('home'));
    $this->followRedirects($response)->assertStatus(Response::HTTP_OK)
        ->assertDontSee($category->name);
});
