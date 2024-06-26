<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\Order\CreateOrderAction;
use App\Actions\OrderItem\CreateOrderItemAction;
use App\DataObjects\Order\OrderDataObject;
use App\DataObjects\OrderItem\OrderItemDataObject;
use App\Enum\CartStatusEnum;
use App\Models\Cart;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

final class OrderService
{
    public function create(Cart $cart): Order
    {
        $order = (new CreateOrderAction())->handle(
            new OrderDataObject(
                cart: $cart
            )
        );

        $cartItems = $cart->items;
        foreach ($cartItems as $cartItem) {
            (new CreateOrderItemAction())->handle(
                new OrderItemDataObject(
                    order: $order,
                    cartItem: $cartItem
                )
            );
        }
        $cart->status = CartStatusEnum::SOLD;
        $cart->save();

        return $order;
    }

    public function getPictures(Order $order): string
    {
        $items = $order->items;
        $zip = new ZipArchive();
        $filename = mb_strtoupper($order->reference.'.zip');

        $zip->open(
            public_path('/storage/orders/'.$filename),
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        foreach ($items as $item) {
            $i = 1;
            foreach (Storage::allFiles('public/items/'.$item->item->id)
                as $file) {
                $name = basename($file);
                $zip->addFile(
                    public_path(
                        'storage/items/'.$item->item->id.'/'.$name
                    ),
                    $item->item->reference.
                        '_'.
                        $i.
                        '.'.
                        File::extension($file)
                );
                $i++;
            }
        }
        $zip->close();

        return public_path('/storage/orders/'.$filename);
    }

    public function getPdf(Order $order): Response
    {
        App::setLocale($order->user->language);
        $pdf = Pdf::loadView('pdf/order', ['order' => $order]);
        App::setLocale('en');

        return $pdf->stream();
    }

    public function getNewOrderReference(): string
    {
        $year = Carbon::now()->format('y');
        $month = Carbon::now()->format('m');
        $lastOrder = Order::orderBy('created_at', 'desc')->first();
        if ($lastOrder) {
            $lastOrderReference = $lastOrder->reference;
            $lastOrderReference = explode('-', $lastOrderReference);
            $lastOrderReference = $lastOrderReference[1];
            $lastOrderReference = (int) $lastOrderReference;
            $lastOrderReference++;
            $lastOrderReference = mb_str_pad(
                (string)$lastOrderReference,
                4,
                '0',
                STR_PAD_LEFT
            );
            $lastOrderReference = $year.$month.'-'.$lastOrderReference;
        } else {
            $lastOrderReference = $year.$month.'-'.'0001';
        }

        return $lastOrderReference;
    }

    public function getPicturesZip(Order $order): string
    {
        $items = $order->items;
        $zip = new ZipArchive();
        $filename = mb_strtoupper($order->reference.'.zip');

        $zip->open(
            public_path('/storage/orders/'.$filename),
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        foreach ($items as $item) {
            foreach ($item->item->getMedia()
                     as $file) {
                $zip->addFile(
                    $file->getPath(),
                    $file->file_name
                );
            }
        }
        $zip->close();

        return public_path('/storage/orders/'.$filename);
    }
}
