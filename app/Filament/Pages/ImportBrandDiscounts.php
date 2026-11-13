<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enum\UserRoleEnum;
use App\Imports\BrandDiscounts\BrandDiscountImporter;
use App\Models\Brand;
use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Re-imports the spreadsheet produced by the "Export remises marque" action:
 * analyse first (nothing written), then apply when the analysis has no error.
 *
 * @property-read ComponentContainer $form
 */
final class ImportBrandDiscounts extends Page implements HasForms
{
    use InteractsWithForms;

    public ?int $brand_id = null;

    /** @var TemporaryUploadedFile|array<string, TemporaryUploadedFile>|null */
    public mixed $file = null;

    /** @var array{errors: array<int, array{row: int, column: string, message: string}>, summary: array<int, string>}|null */
    public ?array $report = null;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Users';

    protected static ?string $navigationLabel = 'Import remises marque';

    protected static ?string $title = 'Import des remises par marque';

    protected static ?string $slug = 'import-brand-discounts';

    protected static string $view = 'filament.pages.import-brand-discounts';

    public function mount(): void
    {
        abort_unless(UserRoleEnum::ADMIN === auth()->user()?->role, 403);
    }

    public function updatedBrandId(): void
    {
        $this->report = null;
    }

    public function updatedFile(): void
    {
        $this->report = null;
    }

    public function analyse(BrandDiscountImporter $importer): void
    {
        $this->form->validate();

        $this->report = $importer->analyse($this->brand(), $importer->readFile($this->filePath()))->toArray();
    }

    public function apply(BrandDiscountImporter $importer): void
    {
        if (null === $this->report || [] !== $this->report['errors']) {
            return;
        }

        $this->form->validate();
        $report = $importer->apply($this->brand(), $importer->readFile($this->filePath()));

        if ($report->hasErrors()) {
            $this->report = $report->toArray();

            return;
        }

        Notification::make()
            ->title('Remises importées')
            ->body(implode(' · ', $report->toArray()['summary']))
            ->success()
            ->send();

        $this->reset(['file', 'report']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    protected function getFormSchema(): array
    {
        return [
            Select::make('brand_id')
                ->label('Marque')
                ->options(fn() => Brand::orderBy('name')->pluck('name', 'id'))
                ->searchable()
                ->reactive()
                ->required(),
            FileUpload::make('file')
                ->label('Fichier Excel exporté via « Export remises marque »')
                ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                ->storeFiles(false)
                ->reactive()
                ->required(),
        ];
    }

    private function brand(): Brand
    {
        return Brand::findOrFail($this->brand_id);
    }

    private function filePath(): string
    {
        $file = is_array($this->file) ? reset($this->file) : $this->file;

        abort_unless($file instanceof TemporaryUploadedFile, 422);

        return $file->getRealPath();
    }
}
