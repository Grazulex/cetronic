<?php

declare(strict_types=1);

use App\Enum\UserRoleEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->default(UserRoleEnum::CUSTOMER->value);
            $table->boolean('is_actif')->default(false);
            $table->boolean('is_blocked')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {});
    }
};
