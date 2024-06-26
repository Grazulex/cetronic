<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class TransfertFileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:file:transfert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copy pictures from old server to new server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $directory = 'public/items/old';
        //print_r(Storage::directories('public/items'));
        //if (array_search($directory, Storage::directories('public/items'))) {
        $this->info('find folder '.$directory);
        $files = Storage::files($directory);
        if ($files) {
            $this->info('find files '.count($files));
            foreach ($files as $filePath) {
                $file = new UploadedFile(storage_path('app/'.$filePath), basename($filePath));
                $fullFilename = $file->getClientOriginalName();
                $this->info('filename '.$fullFilename);
                $filename = pathinfo($fullFilename, PATHINFO_FILENAME);
                $reference = $filename;
                if (str_contains($reference, '_')) {
                    $reference = explode('_', $reference)[0];
                }
                $this->info('reference '.$reference);
                $item = Item::where('reference', $reference)->first();
                if ($item) {
                    $this->info($item->id);
                    if (Storage::disk('public')->exists('items/'.$item->id)) {
                        $pathSmall = '/public/items/'.$item->id.'/small';
                        $this->info($filePath);
                        $imgFile = Image::make(storage_path('app/'.$filePath));
                        $imgFile->resize(150, 150, function ($constraint): void {
                            $constraint->aspectRatio();
                        })->save(storage_path('app/'.$pathSmall.'/'.$fullFilename));

                        Storage::move($filePath, 'public/items/'.$item->id.'/'.$fullFilename);
                        $this->info('move from '.$filePath.' to /public/items/'.$item->id.'/'.$fullFilename);
                    } else {
                        $path = '/public/items/'.$item->id;
                        Storage::makeDirectory($path, 0755, true, true);
                        $pathSmall = '/public/items/'.$item->id.'/small';
                        Storage::makeDirectory($pathSmall, 0755, true, true);
                        $this->info('make firecvtory to public/items/'.$item->id);

                        $this->info($filePath);
                        $imgFile = Image::make(storage_path('app/'.$filePath));
                        $imgFile->resize(150, 150, function ($constraint): void {
                            $constraint->aspectRatio();
                        })->save(storage_path('app/'.$pathSmall.'/'.$fullFilename));

                        Storage::move($filePath, 'public/items/'.$item->id.'/'.$fullFilename);
                        $this->info('move from '.$filePath.' to public/items/'.$item->id.'/'.$fullFilename);
                    }
                }
                $this->newLine();
            }
            exit;
            $files = Storage::files($directory);
            foreach ($files as $filePath) {
                $file = new UploadedFile(storage_path('app/'.$filePath), basename($filePath));
                $fullFilename = $file->getClientOriginalName();
                $this->info('filename'.$fullFilename);
                $filename = pathinfo($fullFilename, PATHINFO_FILENAME);
                $reference = $filename;
                if (str_contains($reference, '_')) {
                    $reference = explode('_', $reference)[0];
                } else {
                    if (str_contains($reference, '-')) {
                        $reference = explode('-', $reference)[0];
                    } else {
                        if (str_contains($reference, ' ')) {
                            $reference = explode(' ', $reference)[0];
                        }
                    }
                }
                $this->info('reference'.$reference);
                $item = Item::where('reference', $reference)->first();
                if ($item) {
                    $this->info($item->id);
                    if (Storage::disk('public')->exists('items/'.$item->id)) {
                        $pathSmall = '/public/items/'.$item->id.'/small';
                        $this->info($filePath);
                        $imgFile = Image::make(storage_path('app/'.$filePath));
                        $imgFile->resize(150, 150, function ($constraint): void {
                            $constraint->aspectRatio();
                        })->save(storage_path('app/'.$pathSmall.'/'.$fullFilename));

                        Storage::move($filePath, 'public/items/'.$item->id.'/'.$fullFilename);
                        $this->info('move from '.$filePath.' to /public/items/'.$item->id.'/'.$fullFilename);
                    } else {
                        $path = '/public/items/'.$item->id;
                        Storage::makeDirectory($path, 0755, true, true);

                        $pathSmall = '/public/items/'.$item->id.'/small';
                        Storage::makeDirectory($pathSmall, 0755, true, true);

                        $this->info('make firecvtory to public/items/'.$item->id);

                        $imgFile = Image::make(storage_path('app/'.$filePath));
                        $imgFile->resize(150, 150, function ($constraint): void {
                            $constraint->aspectRatio();
                        })->save(storage_path('app/'.$pathSmall.'/'.$fullFilename));


                        Storage::move($filePath, 'public/items/'.$item->id.'/'.$fullFilename);
                        $this->info('move from '.$filePath.' to public/items/'.$item->id.'/'.$fullFilename);
                    }
                }
                $this->newLine();
            }
        }
        //} else {
        //    $this->error('dont find folder '.$directory);
        //}

        return Command::SUCCESS;
    }
}
