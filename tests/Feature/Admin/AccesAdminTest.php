<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Response;

it('customer can not go to admin page', function (): void {
    loginAsUser();
    $this->get(filament()->getUrl())
        ->assertStatus(Response::HTTP_FORBIDDEN);
});


it('admin can go to admin page', function (): void {
    loginAsAdmin();
    $this->get(filament()->getUrl())
        ->assertStatus(Response::HTTP_OK);
});
