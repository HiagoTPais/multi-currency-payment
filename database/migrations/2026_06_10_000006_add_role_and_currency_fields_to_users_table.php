<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingColumns = collect(['role', 'country_code', 'currency_code'])
            ->reject(fn (string $column): bool => Schema::hasColumn('users', $column));

        if ($missingColumns->isEmpty()) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($missingColumns): void {
            if ($missingColumns->contains('role')) {
                $table->string('role')->default('employee');
            }

            if ($missingColumns->contains('country_code')) {
                $table->string('country_code', 2)->default('PT');
            }

            if ($missingColumns->contains('currency_code')) {
                $table->string('currency_code', 3)->default('EUR');
            }
        });
    }

    public function down(): void
    {
        $existingColumns = collect(['role', 'country_code', 'currency_code'])
            ->filter(fn (string $column): bool => Schema::hasColumn('users', $column))
            ->values()
            ->all();

        if ($existingColumns === []) {
            return;
        }

        Schema::table('users', function (Blueprint $table) use ($existingColumns): void {
            $table->dropColumn($existingColumns);
        });
    }
};
