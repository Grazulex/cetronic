<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Exports\BrandDiscounts\BrandDiscountsSheet;
use App\Imports\BrandDiscounts\BrandDiscountImporter;
use App\Jobs\ProcessUpdatePriceCart;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\User;
use App\Models\UserBrand;
use App\Models\UserDisable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->otherBrand = Brand::factory()->create(['name' => 'Beta']);
    $this->rings = Category::factory()->create(['name' => 'Rings']);
    $this->watches = Category::factory()->create(['name' => 'Watches']);
    $this->clocks = Category::factory()->create(['name' => 'Clocks']);

    $this->ringA = Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->rings->id]);
    $this->ringB = Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->rings->id]);
    $this->watch = Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->watches->id]);
    $this->clock = Item::factory()->create(['brand_id' => $this->otherBrand->id, 'category_id' => $this->clocks->id]);

    $this->bob = User::factory()->create(['role' => 'customer', 'name' => 'Bob', 'email' => 'bob@example.com', 'external_reference' => 'C-002', 'franco' => 150, 'shipping_price' => 12.5]);
    $this->anna = User::factory()->create(['role' => 'customer', 'name' => 'Anna', 'email' => 'anna@example.com', 'external_reference' => 'C-001']);

    $this->bobRings = importRule(['user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id, 'reduction' => 12.5, 'addition_price' => 1, 'price_type' => PriceTypeEnum::B2B]);

    $this->importer = app(BrandDiscountImporter::class);
});

function importRule(array $attributes): UserBrand
{
    $rule = new UserBrand(array_merge([
        'reduction' => 0, 'coefficient' => 0, 'addition_price' => 0,
        'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false, 'is_excluded' => false,
    ], $attributes));
    $rule->saveQuietly();

    return $rule;
}

/** Rows exactly as the export produces them: headings first. */
function exportedRows(Brand $brand): array
{
    $sheet = new BrandDiscountsSheet($brand);

    return [$sheet->headings(), ...$sheet->array()];
}

function setCell(array &$rows, string $email, string $heading, mixed $value): void
{
    $column = array_search($heading, $rows[0], true);
    expect($column)->not->toBeFalse("Unknown heading {$heading}");

    foreach ($rows as $index => $row) {
        if ($index > 0 && $email === $row[3]) {
            $rows[$index][$column] = $value;

            return;
        }
    }

    throw new RuntimeException("No row for {$email}");
}

function ringsHeading(string $label): string
{
    return 'Rings [' . test()->rings->id . "] {$label}";
}

function watchesHeading(string $label): string
{
    return 'Watches [' . test()->watches->id . "] {$label}";
}

it('writes nothing on an unchanged round trip', function (): void {
    $before = DB::table('user_brands')->get();

    $report = $this->importer->apply($this->brand, exportedRows($this->brand));

    expect($report->hasErrors())->toBeFalse()
        ->and($report->rulesCreated)->toBe(0)
        ->and($report->rulesUpdated)->toBe(0)
        ->and($report->usersUpdated)->toBe(0)
        ->and(DB::table('user_brands')->get())->toEqual($before);
});

it('updates an existing rule', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), 15);

    $report = $this->importer->apply($this->brand, $rows);

    expect($report->rulesUpdated)->toBe(1)
        ->and($this->bobRings->fresh()->reduction)->toEqual(15)
        ->and($this->bobRings->fresh()->price_type)->toBe(PriceTypeEnum::B2B);
});

it('creates a rule with defaults when a cell is filled for a category without rule', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', watchesHeading('Réduction %'), 5);

    $report = $this->importer->apply($this->brand, $rows);
    $rule = UserBrand::where('user_id', $this->bob->id)->where('category_id', $this->watches->id)->sole();

    expect($report->rulesCreated)->toBe(1)
        ->and($rule->reduction)->toEqual(5)
        ->and($rule->coefficient)->toEqual(0)
        ->and($rule->addition_price)->toEqual(0)
        ->and($rule->price_type)->toBe(PriceTypeEnum::DEFAULT)
        ->and($rule->not_show_promo)->toBeFalse()
        ->and($rule->is_excluded)->toBeFalse()
        ->and($rule->category_meta_id)->toBeNull();
});

it('treats empty cells as unchanged and zero as a value', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), null);
    setCell($rows, 'bob@example.com', ringsHeading('Ajout €'), 0);

    $this->importer->apply($this->brand, $rows);

    expect($this->bobRings->fresh()->reduction)->toEqual(12.5)
        ->and($this->bobRings->fresh()->addition_price)->toEqual(0)
        ->and(UserBrand::where('user_id', $this->anna->id)->exists())->toBeFalse();
});

it('updates external reference, franco and shipping price only', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', 'Nom', 'Robert');
    setCell($rows, 'bob@example.com', 'Référence externe', 'C-999');
    setCell($rows, 'bob@example.com', 'Franco', 200);
    setCell($rows, 'bob@example.com', 'Frais de port', 9.9);
    setCell($rows, 'bob@example.com', 'Email', 'robert@example.com');


    $report = $this->importer->apply($this->brand, $rows);
    $bob = $this->bob->fresh();

    expect($report->usersUpdated)->toBe(1)
        ->and($bob->name)->toBe('Bob')
        ->and($bob->email)->toBe('bob@example.com')
        ->and($bob->external_reference)->toBe('C-999')
        ->and($bob->franco)->toEqual(200)
        ->and($bob->shipping_price)->toEqual(9.9);
});

it('never touches meta rules', function (): void {
    $meta = CategoryMeta::factory()->create(['category_id' => $this->rings->id]);
    $metaRule = importRule(['user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id, 'category_meta_id' => $meta->id, 'category_meta_value' => 'Gold', 'reduction' => 30]);
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), 20);

    $this->importer->apply($this->brand, $rows);

    expect($metaRule->fresh()->reduction)->toEqual(30)
        ->and($this->bobRings->fresh()->reduction)->toEqual(20);
});

it('reports duplicate rules instead of guessing', function (): void {
    importRule(['user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id, 'reduction' => 40]);
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), 20);

    $report = $this->importer->apply($this->brand, $rows);

    expect($report->hasErrors())->toBeTrue()
        ->and($report->errors[0]['message'])->toContain('user-brands:dedupe')
        ->and(UserBrand::where('reduction', 2000)->exists())->toBeFalse();
});

it('validates values', function (string $heading, mixed $value): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', 'Franco', 999);
    setCell($rows, 'bob@example.com', str_starts_with($heading, 'r:') ? ringsHeading(mb_substr($heading, 2)) : $heading, $value);

    $report = $this->importer->apply($this->brand, $rows);

    expect($report->errors)->toHaveCount(1)
        ->and($report->errors[0]['row'])->toBe(3)
        ->and($this->bob->fresh()->franco)->toEqual(150);
})->with([
    'reduction over 100' => ['r:Réduction %', 150],
    'negative coefficient' => ['r:Coefficient', -1],
    'text addition' => ['r:Ajout €', 'abc'],
    'unknown price type' => ['r:Type de prix', 'promo'],
    'unknown boolean' => ['r:Exclu', 'peut-être'],
    'negative franco' => ['Frais de port', -5],
]);

it('reports unknown customers', function (): void {
    $rows = exportedRows($this->brand);
    $rows[] = array_replace($rows[1], [0 => 999999]);

    $report = $this->importer->analyse($this->brand, $rows);

    expect($report->errors)->toHaveCount(1)
        ->and($report->errors[0]['row'])->toBe(4)
        ->and($report->errors[0]['column'])->toBe('ID client');
});

it('syncs disabled items when exclusion changes without firing the observer', function (): void {
    Queue::fake();
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Exclu'), 'oui');

    $this->importer->apply($this->brand, $rows);

    expect(UserDisable::where('user_id', $this->bob->id)->pluck('item_id')->sort()->values()->all())
        ->toBe([$this->ringA->id, $this->ringB->id]);
    Queue::assertNotPushed(ProcessUpdatePriceCart::class);

    setCell($rows, 'bob@example.com', ringsHeading('Exclu'), 'non');
    $this->importer->apply($this->brand, $rows);

    expect(UserDisable::where('user_id', $this->bob->id)->count())->toBe(0);
});

it('refreshes only items of the brand in the customer open carts', function (): void {
    Queue::fake();
    $open = Cart::factory()->create(['user_id' => $this->bob->id, 'status' => 'open']);
    $sold = Cart::factory()->create(['user_id' => $this->bob->id, 'status' => 'sold']);
    CartItem::factory()->create(['cart_id' => $open->id, 'item_id' => $this->ringA->id]);
    CartItem::factory()->create(['cart_id' => $open->id, 'item_id' => $this->clock->id]);
    CartItem::factory()->create(['cart_id' => $sold->id, 'item_id' => $this->watch->id]);
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), 20);

    $this->importer->apply($this->brand, $rows);

    Queue::assertPushed(ProcessUpdatePriceCart::class, 1);
    Queue::assertPushed(ProcessUpdatePriceCart::class, fn(ProcessUpdatePriceCart $job): bool => $job->item->is($this->ringA) && $job->user->is($this->bob));
});

it('treats comma decimals and float noise as unchanged', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), '12,5');
    setCell($rows, 'bob@example.com', ringsHeading('Ajout €'), 0.99999999999);
    setCell($rows, 'bob@example.com', 'Frais de port', '12,50');

    $report = $this->importer->analyse($this->brand, $rows);

    expect($report->hasErrors())->toBeFalse()
        ->and($report->rulesUpdated)->toBe(0)
        ->and($report->usersUpdated)->toBe(0);
});

it('maps columns by heading not position', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Réduction %'), 33);
    $rows = array_map(fn(array $row): array => array_reverse($row), $rows);
    $rows = array_map(fn(array $row, int $i): array => [...$row, 0 === $i ? 'Commentaire' : 'note'], $rows, array_keys($rows));

    $report = $this->importer->apply($this->brand, $rows);

    expect($report->hasErrors())->toBeFalse()
        ->and($this->bobRings->fresh()->reduction)->toEqual(33);
});

it('rejects malformed category headings', function (): void {
    $rows = exportedRows($this->brand);
    $rows[0][7] = 'Rings [' . $this->rings->id . '] Remise';

    expect($this->importer->analyse($this->brand, $rows)->errors[0])
        ->toMatchArray(['row' => 1, 'column' => 'Rings [' . $this->rings->id . '] Remise']);
});

it('normalises price type and booleans', function (): void {
    $rows = exportedRows($this->brand);
    setCell($rows, 'bob@example.com', ringsHeading('Type de prix'), ' SPECIAL_1 ');
    setCell($rows, 'bob@example.com', ringsHeading('Masquer promo'), 'Oui');
    setCell($rows, 'bob@example.com', ringsHeading('Exclu'), 0);

    $report = $this->importer->apply($this->brand, $rows);
    $rule = $this->bobRings->fresh();

    expect($report->hasErrors())->toBeFalse()
        ->and($rule->price_type)->toBe(PriceTypeEnum::SPECIAL_1)
        ->and($rule->not_show_promo)->toBeTrue()
        ->and($rule->is_excluded)->toBeFalse();
});

it('rejects a file whose brand or categories do not match', function (): void {
    $wrongBrand = exportedRows($this->brand);
    $foreignCategory = exportedRows($this->brand);
    $foreignCategory[0][7] = 'Clocks [' . $this->clocks->id . '] Réduction %';
    setCell($wrongBrand, 'bob@example.com', ringsHeading('Réduction %'), 50);

    expect($this->importer->apply($this->otherBrand, $wrongBrand)->hasErrors())->toBeTrue()
        ->and($this->importer->apply($this->brand, $foreignCategory)->hasErrors())->toBeTrue()
        ->and($this->bobRings->fresh()->reduction)->toEqual(12.5);
});

it('skips blank rows', function (): void {
    $rows = exportedRows($this->brand);
    $rows[] = array_fill(0, count($rows[0]), null);
    $rows[] = array_fill(0, count($rows[0]), '');

    $report = $this->importer->analyse($this->brand, $rows);

    expect($report->hasErrors())->toBeFalse()
        ->and($report->unchanged)->toBe(2);
});

it('round-trips a real exported xlsx file without changes', function (): void {
    importRule(['user_id' => $this->anna->id, 'brand_id' => $this->brand->id, 'category_id' => $this->watches->id, 'reduction' => 7.25, 'coefficient' => 1.1, 'not_show_promo' => true]);
    $path = tempnam(sys_get_temp_dir(), 'brand-discounts') . '.xlsx';
    file_put_contents($path, Maatwebsite\Excel\Facades\Excel::raw(new App\Exports\BrandDiscounts\BrandDiscountsExport($this->brand), Maatwebsite\Excel\Excel::XLSX));

    $report = $this->importer->analyse($this->brand, $this->importer->readFile($path));
    unlink($path);

    expect($report->errors)->toBe([])
        ->and($report->rulesCreated + $report->rulesUpdated + $report->usersUpdated)->toBe(0)
        ->and($report->unchanged)->toBe(2);
});

it('ignores surrounding spaces in the stored external reference', function (): void {
    $this->bob->forceFill(['external_reference' => 'C-002 '])->saveQuietly();

    $report = $this->importer->analyse($this->brand, exportedRows($this->brand));

    expect($report->usersUpdated)->toBe(0)
        ->and($report->unchanged)->toBe(2);
});

it('accepts an out-of-range value that is unchanged', function (): void {
    DB::table('users')->where('id', $this->bob->id)->update(['franco' => -100]);
    DB::table('user_brands')->where('id', $this->bobRings->id)->update(['reduction' => 15000]);

    $report = $this->importer->analyse($this->brand, exportedRows($this->brand));

    expect($report->errors)->toBe([])
        ->and($report->usersUpdated + $report->rulesUpdated)->toBe(0);
});
