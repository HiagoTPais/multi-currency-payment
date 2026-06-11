<?php

namespace Database\Seeders;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->ensurePassportPersonalAccessClient();

        $finance = User::query()->updateOrCreate(['email' => 'finance@example.com'], [
            'name' => 'Finance Manager',
            'password' => 'password',
            'role' => 'finance',
            'country_code' => 'PT',
            'currency_code' => 'EUR',
        ]);

        $employees = collect([
            ['name' => 'Ana Portugal', 'email' => 'ana@example.com', 'country_code' => 'PT', 'currency_code' => 'EUR'],
            ['name' => 'Bruno Brasil', 'email' => 'bruno@example.com', 'country_code' => 'BR', 'currency_code' => 'BRL'],
            ['name' => 'Charlotte USA', 'email' => 'charlotte@example.com', 'country_code' => 'US', 'currency_code' => 'USD'],
            ['name' => 'George UK', 'email' => 'george@example.com', 'country_code' => 'GB', 'currency_code' => 'GBP'],
            ['name' => 'Hana Japan', 'email' => 'hana@example.com', 'country_code' => 'JP', 'currency_code' => 'JPY'],
            ['name' => 'Noah Canada', 'email' => 'noah@example.com', 'country_code' => 'CA', 'currency_code' => 'CAD'],
        ])->map(fn (array $employee): User => User::query()->updateOrCreate(['email' => $employee['email']], [
            ...$employee,
            'password' => 'password',
            'role' => 'employee',
        ]));

        if (PaymentRequest::query()->exists()) {
            return;
        }

        $rates = ['EUR' => 1.0, 'BRL' => 5.85, 'USD' => 1.08, 'GBP' => 0.86, 'JPY' => 168.2, 'CAD' => 1.48];
        $statuses = PaymentRequestStatus::cases();

        $employees->each(function (User $employee, int $index) use ($finance, $rates, $statuses): void {
            $status = $statuses[$index % count($statuses)];
            $amountLocal = 250 + ($index * 125);
            $approved = in_array($status, [PaymentRequestStatus::Approved, PaymentRequestStatus::Rejected], true);

            PaymentRequest::query()->create([
                'user_id' => $employee->id,
                'amount_local' => $amountLocal,
                'currency_code' => $employee->currency_code,
                'exchange_rate' => $rates[$employee->currency_code],
                'exchange_rate_source' => 'Seeder',
                'exchange_rate_timestamp' => now()->subHours($index + 1),
                'amount_eur' => round($amountLocal / $rates[$employee->currency_code], 2),
                'status' => $status,
                'approved_by' => $approved ? $finance->id : null,
                'approved_at' => $approved ? now()->subMinutes(30) : null,
                'expires_at' => $status === PaymentRequestStatus::Expired ? now()->subHour() : now()->addHours(48),
                'notes' => 'Seeded request for demo purposes.',
                'created_at' => $status === PaymentRequestStatus::Expired ? now()->subHours(49) : now(),
                'updated_at' => now(),
            ]);
        });
    }

    private function ensurePassportPersonalAccessClient(): void
    {
        $clientExists = Passport::client()
            ->newQuery()
            ->where('provider', 'users')
            ->where('revoked', false)
            ->latest()
            ->get()
            ->contains(fn ($client): bool => $client->hasGrantType('personal_access'));

        if (! $clientExists) {
            app(ClientRepository::class)->createPersonalAccessGrantClient(
                'Multi-Currency Payment Personal Access Client',
                'users',
            );
        }
    }
}
