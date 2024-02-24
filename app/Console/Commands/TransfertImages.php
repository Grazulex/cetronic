<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class TransfertImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:images:media';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'transfert images to media';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $directory = 'public/items';
        $this->info('find folder '.$directory);
        $files = Storage::files($directory, true);
        if ($files) {
            $this->info('find files '.count($files));
            foreach ($files as $filePath) {
                $file = new UploadedFile(storage_path('app/'.$filePath), basename($filePath));
                $id = basename($file->getPath());
                $item = Item::where('id', $id)->first();
                if ($item) {
                    $fullFilename = $file->getClientOriginalName();
                    $filename = pathinfo($fullFilename, PATHINFO_FILENAME);
                    $reference = $filename;
                    $order = $item->media->count()+1;
                    if (str_contains($reference, '_')) {
                        $parts= explode('_', $reference);
                        $order = (int)$parts[count($parts)-1];
                    }

                    $this->info('find item '.$item->id);
                    try {
                        $item->addMedia($file)
                            ->setOrder($order)
                            ->preservingOriginal()
                            ->toMediaCollection('default', 'items');
                    } catch (FileDoesNotExist|FileIsTooBig $e) {
                    }
                }
            }

            $this->info('end');
        }
        return Command::SUCCESS;
    }
}
