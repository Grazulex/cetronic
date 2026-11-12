<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Exports\BrandDiscounts\BrandDiscountColumns;
use App\Exports\BrandDiscounts\BrandDiscountsExport;
use App\Exports\BrandDiscounts\BrandDiscountsSheet;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->otherBrand = Brand::factory()->create(['name' => 'Beta']);

    $this->watches = Category::factory()->create(['name' => 'Watches']);
    $this->rings = Category::factory()->create(['name' => 'Rings']);
    $this->straps = Category::factory()->create(['name' => 'Straps']);
    $this->clocks = Category::factory()->create(['name' => 'Clocks']);

    Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->watches->id]);
    Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->rings->id]);
    Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->straps->id, 'is_published' => false]);
    Item::factory()->create(['brand_id' => $this->otherBrand->id, 'category_id' => $this->clocks->id]);

    $this->bob = User::factory()->create(['role' => 'customer', 'name' => 'Bob', 'email' => 'bob@example.com', 'external_reference' => 'C-002', 'franco' => 150, 'shipping_price' => 12.5]);
    $this->anna = User::factory()->create(['role' => 'customer', 'name' => 'Anna', 'email' => 'anna@example.com', 'external_reference' => 'C-001']);
});

function rule(array $attributes): UserBrand
{
    $rule = new UserBrand(array_merge([
        'reduction' => 0,
        'coefficient' => 0,
        'addition_price' => 0,
        'price_type' => PriceTypeEnum::DEFAULT,
        'not_show_promo' => false,
        'is_excluded' => false,
    ], $attributes));
    $rule->saveQuietly();

    return $rule;
}

function sheetRow(array $rows, string $email): array
{
    return collect($rows)->firstWhere(3, $email) ?? throw new RuntimeException("No row for {$email}");
}

it('lists only categories holding published items of the brand, by name', function (): void {
    expect(BrandDiscountColumns::categoriesFor($this->brand)->pluck('name')->all())->toBe(['Rings', 'Watches']);
});

it('exports active customers only, by name', function (): void {
    User::factory()->create(['role' => 'customer', 'is_actif' => false, 'email' => 'inactive@example.com']);
    User::factory()->create(['role' => 'agent', 'email' => 'agent@example.com']);
    User::factory()->create(['role' => 'admin', 'email' => 'admin@example.com']);
    User::factory()->create(['role' => 'customer', 'email' => 'deleted@example.com'])->delete();

    $emails = array_column((new BrandDiscountsSheet($this->brand))->array(), 3);

    expect($emails)->toBe(['anna@example.com', 'bob@example.com']);
});

it('builds headings with category ids', function (): void {
    $r = $this->rings->id;
    $w = $this->watches->id;

    expect((new BrandDiscountsSheet($this->brand))->headings())->toBe([
        'ID client', 'ID marque', 'Nom', 'Email', 'Référence externe', 'Franco', 'Frais de port',
        "Rings [{$r}] Réduction %", "Rings [{$r}] Coefficient", "Rings [{$r}] Ajout €", "Rings [{$r}] Type de prix", "Rings [{$r}] Masquer promo", "Rings [{$r}] Exclu",
        "Watches [{$w}] Réduction %", "Watches [{$w}] Coefficient", "Watches [{$w}] Ajout €", "Watches [{$w}] Type de prix", "Watches [{$w}] Masquer promo", "Watches [{$w}] Exclu",
    ]);
});

it('fills rule values and leaves categories without rule empty', function (): void {
    rule([
        'user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id,
        'reduction' => 12.5, 'coefficient' => 0, 'addition_price' => 1,
        'price_type' => PriceTypeEnum::B2B, 'not_show_promo' => true, 'is_excluded' => false,
    ]);

    $row = sheetRow((new BrandDiscountsSheet($this->brand))->array(), 'bob@example.com');

    expect(array_slice($row, 0, 7))->toEqual([$this->bob->id, $this->brand->id, 'Bob', 'bob@example.com', 'C-002', 150, 12.5])
        ->and(array_slice($row, 7, 6))->toEqual([12.5, 0, 1, 'b2b', 'oui', 'non'])
        ->and(array_slice($row, 13, 6))->toBe([null, null, null, null, null, null]);
});

it('ignores meta rules, brand-wide rules and rules of other brands', function (): void {
    $meta = CategoryMeta::factory()->create(['category_id' => $this->rings->id]);
    rule(['user_id' => $this->anna->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id, 'category_meta_id' => $meta->id, 'category_meta_value' => 'Gold', 'reduction' => 30]);
    rule(['user_id' => $this->anna->id, 'brand_id' => $this->brand->id, 'category_id' => null, 'reduction' => 40]);
    rule(['user_id' => $this->anna->id, 'brand_id' => $this->otherBrand->id, 'category_id' => $this->rings->id, 'reduction' => 50]);

    $row = sheetRow((new BrandDiscountsSheet($this->brand))->array(), 'anna@example.com');

    expect(array_filter(array_slice($row, 7), fn($value): bool => null !== $value))->toBe([]);
});

it('parses its own headings back', function (): void {
    expect(BrandDiscountColumns::parse('Rings [12] Type de prix'))->toBe(['category_id' => 12, 'field' => 'price_type'])
        ->and(BrandDiscountColumns::parse('Bagues [3] fines [7] Réduction %'))->toBe(['category_id' => 7, 'field' => 'reduction'])
        ->and(BrandDiscountColumns::parse('Rings [12]  Exclu  '))->toBe(['category_id' => 12, 'field' => 'is_excluded'])
        ->and(BrandDiscountColumns::parse('Rings [12] Commentaire'))->toBeNull()
        ->and(BrandDiscountColumns::parse('Commentaire'))->toBeNull();
});

it('adds a legend sheet', function (): void {
    $sheets = (new BrandDiscountsExport($this->brand))->sheets();

    expect($sheets)->toHaveCount(2)
        ->and($sheets[0]->title())->toBe('Remises')
        ->and($sheets[1]->title())->toBe('Légende');
});

it('downloads the file from the users list for admins', function (): void {
    Carbon::setTestNow('2026-11-12 10:00:00');
    Excel::fake();
    loginAsAdmin();

    livewire(ListUsers::class)->callAction('exportBrandDiscounts', ['brand_id' => $this->brand->id]);

    Excel::assertDownloaded('Remises_alpha_2026-11-12_100000.xlsx', fn(BrandDiscountsExport $export): bool => $export->brand->is($this->brand));
    Carbon::setTestNow();
});

it('renders a real workbook with frozen panes and dropdowns', function (): void {
    rule(['user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id, 'reduction' => 10]);

    $path = tempnam(sys_get_temp_dir(), 'brand-discounts') . '.xlsx';
    file_put_contents($path, Excel::raw(new BrandDiscountsExport($this->brand), Maatwebsite\Excel\Excel::XLSX));
    $workbook = PhpOffice\PhpSpreadsheet\IOFactory::load($path);
    $sheet = $workbook->getSheetByName('Remises');

    expect($workbook->getSheetNames())->toBe(['Remises', 'Légende'])
        ->and($sheet->getFreezePane())->toBe('E2')
        ->and($sheet->getCell('K1')->getValue())->toBe("Rings [{$this->rings->id}] Type de prix")
        ->and($sheet->getCell('K2')->getDataValidation()->getFormula1())->toBe('"default,b2b,special_1,special_2,special_3,fix"')
        ->and($sheet->getCell('L3')->getDataValidation()->getFormula1())->toBe('"oui,non"')
        ->and($sheet->getCell('H3')->getValue())->toEqual(10)
        ->and($sheet->getCell('I3')->getValue())->toBe(0)
        ->and($sheet->getCell('F2')->getValue())->toBe(0)
        ->and($sheet->getCell('N3')->getValue())->toBeNull();

    unlink($path);
});
