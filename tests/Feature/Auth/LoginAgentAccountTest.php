<?php

declare(strict_types=1);

it('can see catalogue download', function (): void {
    $user = loginAsAgent();
    $this->get(uri: route('home'))
        ->assertSee(__('nav.cataloge_download'));
});
