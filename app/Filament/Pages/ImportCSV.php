<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enum\UserRoleEnum;
use App\Imports\ItemsImport;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Pages\Page;
use Maatwebsite\Excel\HeadingRowImport;

final class ImportCSV extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-upload';

    protected static ?string $navigationGroup = 'Upload';

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === UserRoleEnum::ADMIN;
    }

    protected static string $view = 'filament.pages.import-c-s-v';

    public array $csv= [];

    public int $brand_id;

    public int $category_id;

    protected array $rules = [
        'category_id' => 'required',
        'csv' => 'required'
    ];

    public function submit(): Redirector|RedirectResponse
    {
        $this->validate();
        $files = $this->csv;
        foreach ($files as $file) {
            $headings = (new HeadingRowImport())->toArray($file);
            Excel::import(new ItemsImport($this->category_id, $headings), $file);
        }

        return redirect()->route(route: 'filament.resources.items.index')->with(key: 'success', value: 'CSV imported successfully!');
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
