<?php

declare(strict_types=1);

use App\Models\Message;
use Symfony\Component\HttpFoundation\Response;

/*
 * The message written by the client in the admin is shown on the homepage in
 * the site's style; around the year-end holidays it gets a festive touch.
 */

function homeMessage(string $start, string $end): Message
{
    return Message::create([
        'start_date' => $start,
        'end_date' => $end,
        'local' => 'en',
        'title' => 'Busy period',
        'content' => '<p><strong>Dear partner,</strong></p>',
    ]);
}

it('shows the active message in a card', function (): void {
    $this->travelTo('2026-07-10 10:00');
    homeMessage('2026-07-01', '2026-07-31');

    $this->get(route('home'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('class="home-message"', false)
        ->assertSee('Busy period')
        ->assertSee('<strong>Dear partner,</strong>', false)
        ->assertDontSee('home-message--festive');
});

it('gives the message a festive touch during the holiday season', function (string $today): void {
    $this->travelTo($today);
    homeMessage('2026-11-01', '2027-01-31');

    $this->get(route('home'))->assertSee('home-message--festive');
})->with(['2026-11-25 10:00', '2026-12-31 23:00', '2027-01-06 12:00']);

it('keeps the plain card outside the holiday season', function (string $today): void {
    $this->travelTo($today);
    homeMessage('2026-11-01', '2027-01-31');

    $this->get(route('home'))
        ->assertSee('class="home-message"', false)
        ->assertDontSee('home-message--festive');
})->with(['2026-11-14 10:00', '2027-01-07 10:00']);

it('shows nothing when no message is active', function (): void {
    $this->travelTo('2026-08-10 10:00');
    homeMessage('2026-07-01', '2026-07-31');

    $this->get(route('home'))->assertDontSee('home-message');
});
