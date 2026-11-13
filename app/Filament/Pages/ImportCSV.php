<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Resources\ItemResource;
use App\Enum\UserRoleEnum;
use App\Imports\ItemsImport;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

final class ImportCSV extends Page implements HasForms
{
    use InteractsWithForms;

    public array $csv = [];

    public int $brand_id;

    public int $category_id;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationGroup = 'Imports';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Import CSV';

    protected static string $view = 'filament.pages.import-c-s-v';

    protected array $rules = [
        'category_id' => 'required',
        'csv' => 'required',
    ];

    public function submit(): Redirector|RedirectResponse
    {
        $this->validate();
        $files = $this->csv;
        foreach ($files as $file) {
            $headings = (new HeadingRowImport())->toArray($file);
            Excel::import(new ItemsImport($this->category_id, $headings), $file);
        }

        return redirect(ItemResource::getUrl('index'))->with(key: 'success', value: 'CSV imported successfully!');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return UserRoleEnum::ADMIN === auth()->user()?->role;
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make(name: 'category_id')
                ->options(Category::all()->pluck(value: 'name', key: 'id'))
                ->required()
                ->searchable(),
            Forms\Components\FileUpload::make(name: 'csv')->required(),
        ];
    }
}
