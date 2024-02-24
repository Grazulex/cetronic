<?php

declare(strict_types=1);

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type')->default(LocationTypeEnum::SHIPPING->value);
            $table->string('company')->nullable();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('vat')->nullable();
            $table->string('street');
            $table->string('street_number')->nullable();
            $table->string('zip');
            $table->string('city');
            $table->string('country', 3)->default(CountryEnum::BELGIUM->value);
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
