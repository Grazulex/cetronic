<?php

declare(strict_types=1);

use App\Enum\PriceTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_brands', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('brand_id')->constrained('brands')->onDelete('cascade');
            $table->integer('reduction')->default(0);
            $table->string('price_type')->default(PriceTypeEnum::DEFAULT->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_brands');
    }
};
