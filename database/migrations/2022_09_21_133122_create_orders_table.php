<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Enum\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('agent_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('status')->default(OrderStatusEnum::OPEN->value);
            $table->string('reference')->unique();
            $table->string('tracking_number')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('shipping_company')->nullable();
            $table->string('shipping_name')->nullable();
            $table->string('shipping_street');
            $table->string('shipping_street_number')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_zip');
            $table->string('shipping_country', 3)->default(CountryEnum::BELGIUM->value);
            $table->integer('total_price')->default(0);
            $table->integer('total_price_with_tax')->default(0);
            $table->integer('total_tax')->default(0);
            $table->integer('total_shipping')->default(0);
            $table->integer('total_shipping_with_tax')->default(0);
            $table->integer('total_shipping_tax')->default(0);
            $table->integer('total_products')->default(0);
            $table->integer('total_products_with_tax')->default(0);
            $table->integer('total_products_tax')->default(0);
            $table->string('invoice_email')->nullable();
            $table->string('invoice_company')->nullable();
            $table->string('invoice_name')->nullable();
            $table->string('invoice_street');
            $table->string('invoice_street_number')->nullable();
            $table->string('invoice_city');
            $table->string('invoice_zip');
            $table->string('invoice_country', 3)->default(CountryEnum::BELGIUM->value);
            $table->string('invoice_vat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
