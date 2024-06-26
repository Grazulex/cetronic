<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enum\UserRoleEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Http\RedirectResponse;
use Livewire\Redirector;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

final class ImportPictures extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-photograph';

    protected static ?string $navigationGroup = 'Upload';

    protected static string $view = 'filament.pages.import-pictures';

    protected static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === UserRoleEnum::ADMIN;
    }

    public array $pictures = [];

    public int $brand_id;

    public int $category_id;

    protected array $rules = [
        'brand_id' => 'required',
        'category_id' => 'required',
        'pictures' => 'required',
    ];


    public function submit(): Redirector|RedirectResponse
    {
        $brand = Brand::where('id', $this->brand_id)->first();
        $this->validate();
        $files = $this->pictures;
        foreach ($files as $file) {
            $fullFilename = $file->getClientOriginalName();
            $filename = pathinfo(path: $fullFilename, flags: PATHINFO_FILENAME);
            $reference = $filename;
            if (str_contains($reference, '_')) {
                $reference = explode(separator: '_', string: $reference)[0];
            }
            $item = Item::withTrashed()->where('reference', $reference)->first();
            if ( ! $item) {
                $item = Item::create([
                    'reference' => $reference,
                    'description' => '',
                    'brand_id' => $this->brand_id,
                    'category_id' => $this->category_id,
                    'is_published' => $brand->is_upload_actif,
                ]);
            } else {
                $item->update([
                    'brand_id' => $this->brand_id,
                    'category_id' => $this->category_id,
                    'is_published' => $brand->is_upload_actif,
                ]);
                if ($item->is_new) {
                    $item->update([
                        'is_published' => true,
                    ]);
                }
            }

            if ( ! $item->media->where('file_name', $fullFilename)->first()) {
                $order = $item->media->count()+1;
                if (str_contains($filename, '_')) {
                    $parts= explode(separator: '_', string: $filename);
                    $order = (int)$parts[count($parts)-1];
                }

                try {
                    $item->addMedia($file)
                        ->setOrder($order)
                        ->preservingOriginal()
                        ->toMediaCollection(collectionName: 'default', diskName: 'items');
                } catch (FileDoesNotExist|FileIsTooBig $e) {
                    return redirect()->route(route: 'filament.pages.import-pictures')->with(key: 'error', value: $e->getMessage());
                }
            }
        }

        return redirect()->route(route: 'filament.resources.items.index')->with(key: 'success', value: 'Pictures imported successfully!');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make(name: 'brand_id')
                ->options(Brand::pluck(column: 'name', key: 'id'))
                ->searchable()
                ->required(),
            Forms\Components\Select::make(name: 'category_id')
                ->options(Category::pluck(column: 'name', key: 'id'))
                ->required()
                ->searchable(),
            Forms\Components\FileUpload::make(name: 'pictures')
                ->image()
                ->directory(directory: 'items')
                ->required()
                ->multiple(),
        ];
    }
}
