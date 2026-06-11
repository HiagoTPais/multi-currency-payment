<?php

namespace Database\Factories;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentRequest>
 */
class PaymentRequestFactory extends Factory
{
    public function definition(): array
    {
        $rates = ['EUR' => 1.0, 'BRL' => 5.85, 'USD' => 1.08, 'GBP' => 0.86, 'JPY' => 168.2, 'CAD' => 1.48];
        $currency = fake()->randomElement(array_keys($rates));
        $amountLocal = fake()->randomFloat(2, 50, 5000);

        return [
            'user_id' => User::factory(),
            'amount_local' => $amountLocal,
            'currency_code' => $currency,
            'exchange_rate' => $rates[$currency],
            'exchange_rate_source' => 'Factory',
            'exchange_rate_timestamp' => now(),
            'amount_eur' => round($amountLocal / $rates[$currency], 2),
            'status' => PaymentRequestStatus::Pending,
            'expires_at' => now()->addHours(48),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
