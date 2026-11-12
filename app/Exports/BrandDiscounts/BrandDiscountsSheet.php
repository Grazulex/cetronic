<?php

declare(strict_types=1);

namespace App\Exports\BrandDiscounts;

use App\Enum\PriceTypeEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class BrandDiscountsSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /** @var Collection<int, Category>|null */
    private ?Collection $categories = null;

    public function __construct(public readonly Brand $brand) {}

    public function title(): string
    {
        return 'Remises';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        $headings = array_values(BrandDiscountColumns::USER_FIELDS);

        foreach ($this->categories() as $category) {
            foreach (array_keys(BrandDiscountColumns::FIELDS) as $field) {
                $headings[] = BrandDiscountColumns::heading($category, $field);
            }
        }

        return $headings;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        $rules = UserBrand::query()
            ->where('brand_id', $this->brand->id)
            ->whereNotNull('category_id')
            ->whereNull('category_meta_id')
            ->whereNull('category_meta_value')
            ->get()
            ->keyBy(fn(UserBrand $rule): string => "{$rule->user_id}-{$rule->category_id}");

        return BrandDiscountColumns::customers()->get()
            ->map(function (User $user) use ($rules): array {
                $row = [
                    $user->id,
                    $this->brand->id,
                    $user->name,
                    $user->email,
                    $user->external_reference,
                    $user->franco,
                    $user->shipping_price,
                ];

                foreach ($this->categories() as $category) {
                    array_push($row, ...$this->ruleCells($rules->get("{$user->id}-{$category->id}")));
                }

                return $row;
            })
            ->all();
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => fn(AfterSheet $event) => $this->decorate($event->sheet->getDelegate()),
        ];
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return $this->categories ??= BrandDiscountColumns::categoriesFor($this->brand);
    }

    /**
     * @return array<int, float|int|string|null>
     */
    private function ruleCells(?UserBrand $rule): array
    {
        if (null === $rule) {
            return array_fill(0, count(BrandDiscountColumns::FIELDS), null);
        }

        return [
            $rule->reduction,
            $rule->coefficient,
            $rule->addition_price,
            $rule->price_type->value,
            BrandDiscountColumns::formatBool($rule->not_show_promo),
            BrandDiscountColumns::formatBool($rule->is_excluded),
        ];
    }

    private function decorate(Worksheet $sheet): void
    {
        $lastRow = max(2, $sheet->getHighestDataRow());
        $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));

        $sheet->freezePane('E2');
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:B{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E5E7EB');

        $this->validate($sheet, 'F', $lastRow, $this->decimalValidation());
        $this->validate($sheet, 'G', $lastRow, $this->decimalValidation());

        $priceTypes = implode(',', array_map(fn(PriceTypeEnum $type): string => $type->value, PriceTypeEnum::cases()));
        $booleans = BrandDiscountColumns::TRUE_LABEL . ',' . BrandDiscountColumns::FALSE_LABEL;

        $column = count(BrandDiscountColumns::USER_FIELDS) + 1;
        foreach ($this->categories() as $ignored) {
            foreach (array_keys(BrandDiscountColumns::FIELDS) as $field) {
                $letter = Coordinate::stringFromColumnIndex($column++);
                $validation = match ($field) {
                    'price_type' => $this->listValidation($priceTypes),
                    'not_show_promo', 'is_excluded' => $this->listValidation($booleans),
                    default => $this->decimalValidation(),
                };
                $this->validate($sheet, $letter, $lastRow, $validation);
            }
        }
    }

    private function validate(Worksheet $sheet, string $column, int $lastRow, DataValidation $validation): void
    {
        $sheet->setDataValidation("{$column}2:{$column}{$lastRow}", $validation);
    }

    private function listValidation(string $values): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setAllowBlank(true);
        $validation->setShowDropDown(true);
        $validation->setShowErrorMessage(true);
        $validation->setFormula1('"' . $values . '"');

        return $validation;
    }

    private function decimalValidation(): DataValidation
    {
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $validation->setAllowBlank(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Valeur invalide');
        $validation->setError('Nombre positif attendu (laisser vide pour ne rien changer).');
        $validation->setFormula1('0');

        return $validation;
    }
}
