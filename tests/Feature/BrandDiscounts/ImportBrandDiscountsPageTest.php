<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use App\Exports\BrandDiscounts\BrandDiscountsSheet;
use App\Filament\Pages\ImportBrandDiscounts;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

use function Pest\Livewire\livewire;

use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    $this->brand = Brand::factory()->create(['name' => 'Alpha']);
    $this->rings = Category::factory()->create(['name' => 'Rings']);
    Item::factory()->create(['brand_id' => $this->brand->id, 'category_id' => $this->rings->id]);

    $this->bob = User::factory()->create(['role' => 'customer', 'name' => 'Bob', 'email' => 'bob@example.com']);
    $this->rule = new UserBrand([
        'user_id' => $this->bob->id, 'brand_id' => $this->brand->id, 'category_id' => $this->rings->id,
        'reduction' => 10, 'coefficient' => 0, 'addition_price' => 0,
        'price_type' => PriceTypeEnum::DEFAULT, 'not_show_promo' => false, 'is_excluded' => false,
    ]);
    $this->rule->saveQuietly();
});

/** An uploaded xlsx built from the export, with Bob's Rings reduction set to $reduction. */
function discountsUpload(Brand $brand, Category $rings, mixed $reduction): UploadedFile
{
    $sheet = new BrandDiscountsSheet($brand);
    $headings = $sheet->headings();
    $rows = $sheet->array();
    $rows[0][array_search("Rings [{$rings->id}] Réduction %", $headings, true)] = $reduction;

    $content = Excel::raw(new class ([$headings, ...$rows]) implements FromArray {
        public function __construct(private readonly array $rows) {}

        public function array(): array
        {
            return $this->rows;
        }
    }, ExcelFormat::XLSX);

    return UploadedFile::fake()->createWithContent('remises.xlsx', $content);
}

it('is reserved to admins', function (string $role, int $status): void {
    'admin' === $role ? loginAsAdmin() : ('agent' === $role ? loginAsAgent() : loginAsUser());

    $this->get(ImportBrandDiscounts::getUrl())->assertStatus($status);
})->with([
    'admin' => ['admin', Response::HTTP_OK],
    'agent' => ['agent', Response::HTTP_FORBIDDEN],
    'customer' => ['customer', Response::HTTP_FORBIDDEN],
]);

it('shows the analysis without writing', function (): void {
    loginAsAdmin();

    livewire(ImportBrandDiscounts::class)
        ->set('brand_id', $this->brand->id)
        ->set('file', [discountsUpload($this->brand, $this->rings, 25)])
        ->call('analyse')
        ->assertHasNoErrors()
        ->assertSee('1 remise modifiée')
        ->assertSee('Appliquer');

    expect($this->rule->fresh()->reduction)->toEqual(10);
});

it('applies after a clean analysis', function (): void {
    loginAsAdmin();

    livewire(ImportBrandDiscounts::class)
        ->set('brand_id', $this->brand->id)
        ->set('file', [discountsUpload($this->brand, $this->rings, 25)])
        ->call('analyse')
        ->call('apply')
        ->assertNotified()
        ->assertSet('report', null);

    expect($this->rule->fresh()->reduction)->toEqual(25);
});

it('cannot apply when the analysis has errors', function (): void {
    loginAsAdmin();

    livewire(ImportBrandDiscounts::class)
        ->set('brand_id', $this->brand->id)
        ->set('file', [discountsUpload($this->brand, $this->rings, 150)])
        ->call('analyse')
        ->assertSee('entre 0 et 100')
        ->assertDontSee('Appliquer')
        ->call('apply');

    expect($this->rule->fresh()->reduction)->toEqual(10);
});

it('clears the analysis when the brand changes', function (): void {
    loginAsAdmin();
    $other = Brand::factory()->create();

    livewire(ImportBrandDiscounts::class)
        ->set('brand_id', $this->brand->id)
        ->set('file', [discountsUpload($this->brand, $this->rings, 25)])
        ->call('analyse')
        ->set('brand_id', $other->id)
        ->assertSet('report', null);
});
