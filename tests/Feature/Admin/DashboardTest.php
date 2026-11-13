<?php

declare(strict_types=1);

use App\DataObjects\Stats\SalesFilters;
use App\Filament\Pages\Dashboard;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\UserResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Services\SalesStatsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;

use function Pest\Livewire\livewire;

function euros(float $amount): string
{
    return Number::currency($amount, 'EUR', 'fr');
}

beforeEach(function (): void {
    Carbon::setTestNow('2026-11-13 10:00:00');

    $this->alpha = Brand::factory()->create(['name' => 'Alpha']);
    $this->beta = Brand::factory()->create(['name' => 'Beta']);
    $category = Category::factory()->create(['name' => 'Montres']);
    $this->watch = Item::factory()->create(['reference' => 'REF-WATCH', 'brand_id' => $this->alpha->id, 'category_id' => $category->id]);
    $this->battery = Item::factory()->create(['reference' => 'REF-BATT', 'brand_id' => $this->beta->id, 'category_id' => $category->id]);

    $this->dupont = User::factory()->create(['role' => 'customer', 'name' => 'Bijouterie Dupont']);
    $this->mertens = User::factory()->create(['role' => 'customer', 'name' => 'Horlogerie Mertens']);

    // October, compared range 1–13: 100 € (14 October is outside the comparison).
    createSale($this->watch, 1, 100, '2026-10-05', ['user_id' => $this->dupont->id, 'reference' => 'OCT-1']);
    createSale($this->watch, 1, 500, '2026-10-14', ['user_id' => $this->dupont->id, 'reference' => 'OCT-2']);
    // November 1–13: 150 € — Dupont returns, Mertens is new.
    createSale($this->watch, 1, 90, '2026-11-02', ['user_id' => $this->dupont->id, 'reference' => 'NOV-1']);
    createSale($this->battery, 6, 10, '2026-11-05', ['user_id' => $this->mertens->id, 'reference' => 'NOV-2']);
    createSale($this->watch, 1, 999, '2026-11-06', ['user_id' => $this->mertens->id, 'reference' => 'NOV-DELETED'])->delete();
});

afterEach(fn() => Carbon::setTestNow());

it('shows the sales of the month compared at the same date', function (): void {
    loginAsAdmin();
    $revenue = app(SalesStatsService::class)->summary(new SalesFilters(from: Carbon::parse('2026-11-01'), to: Carbon::parse('2026-11-13')))['revenue'];

    livewire(Dashboard::class)
        ->assertSee(['Novembre 2026', 'Du 1er au 13 novembre · comparé au 1er–13 octobre'])
        ->assertSee(euros($revenue))
        ->assertSee(euros(150))
        ->assertSee('▲ 50 %')
        ->assertSee('dont 1 nouveau');
});

it('lists the top brands, top products and latest orders of the month', function (): void {
    loginAsAdmin();
    $order = App\Models\Order::query()->where('reference', 'NOV-2')->first();

    livewire(Dashboard::class)
        ->assertSeeInOrder(['Top marques du mois', 'Alpha', 'Beta'])
        ->assertSeeInOrder(['Top produits du mois', 'REF-BATT', 'REF-WATCH'])
        ->assertSeeInOrder(['Dernières commandes', 'NOV-2', 'NOV-1'])
        ->assertSeeHtml(OrderResource::getUrl('edit', ['record' => $order]))
        ->assertDontSee('NOV-DELETED')
        ->assertDontSee('OCT-2');
});

it('links the customers waiting for activation, and hides the line when there are none', function (): void {
    loginAsAdmin();
    livewire(Dashboard::class)->assertDontSee('à activer');

    User::factory()->count(2)->create(['role' => 'customer', 'is_actif' => false]);

    livewire(Dashboard::class)
        ->assertSee('2 clients à activer')
        ->assertSeeHtml(e(UserResource::getUrl('index', ['activeTab' => 'a-activer'])));
});

it('shows a past month as a whole and ignores a future month', function (): void {
    loginAsAdmin();

    livewire(Dashboard::class, ['month' => '2026-10'])
        ->assertSee(['Octobre 2026', 'comparé à septembre 2026', euros(600)]);

    livewire(Dashboard::class, ['month' => '2030-01'])->assertSee('Novembre 2026');
});

it('renders a month without sales', function (): void {
    loginAsAdmin();

    livewire(Dashboard::class, ['month' => '2026-06'])
        ->assertOk()
        ->assertSee([euros(0), '—']);
});

it('shows agents a welcome text and no figure', function (): void {
    loginAsAgent();

    $this->get('/admin')
        ->assertOk()
        ->assertSee('Bienvenue')
        ->assertDontSee('Bijouterie Dupont')
        ->assertDontSee('NOV-1')
        ->assertDontSee('€');
});

it('replaces the old dashboard widgets', function (): void {
    expect(class_exists(App\Filament\Widgets\StatsOverview::class))->toBeFalse()
        ->and(class_exists(App\Filament\Widgets\LatestCartsOpen::class))->toBeFalse()
        ->and(class_exists(App\Filament\Widgets\LatestCustomersToActived::class))->toBeFalse();
});
