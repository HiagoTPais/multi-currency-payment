<?php

namespace Tests\Feature;

use App\Enums\PaymentRequestStatus;
use App\Models\PaymentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PaymentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_create_payment_request_with_exchange_snapshot(): void
    {
        Http::fake(['api.exchangerate-api.com/*' => Http::response(['rates' => ['BRL' => 5.0]])]);
        Passport::actingAs($this->employee(['currency_code' => 'BRL', 'country_code' => 'BR']));

        $this->postJson('/api/payment-requests', ['amount_local' => 500])
            ->assertCreated()
            ->assertJsonPath('data.currency_code', 'BRL')
            ->assertJsonPath('data.exchange_rate', 5)
            ->assertJsonPath('data.amount_eur', 100)
            ->assertJsonPath('data.status', 'pending');

        Http::assertSentCount(1);
        $this->assertDatabaseHas('payment_requests', ['exchange_rate' => 5, 'amount_eur' => 100]);
    }

    public function test_creation_fetches_current_exchange_rate_for_each_request(): void
    {
        Http::fake([
            'api.exchangerate-api.com/*' => Http::sequence()
                ->push(['rates' => ['BRL' => 5.0]])
                ->push(['rates' => ['BRL' => 6.0]]),
        ]);
        Passport::actingAs($this->employee(['currency_code' => 'BRL', 'country_code' => 'BR']));

        $this->postJson('/api/payment-requests', ['amount_local' => 600])
            ->assertCreated()
            ->assertJsonPath('data.exchange_rate', 5)
            ->assertJsonPath('data.amount_eur', 120);

        $this->postJson('/api/payment-requests', ['amount_local' => 600])
            ->assertCreated()
            ->assertJsonPath('data.exchange_rate', 6)
            ->assertJsonPath('data.amount_eur', 100);

        Http::assertSentCount(2);
    }

    public function test_provider_failure_prevents_creation(): void
    {
        Http::fake(['api.exchangerate-api.com/*' => Http::response([], 500)]);
        Passport::actingAs($this->employee(['currency_code' => 'USD', 'country_code' => 'US']));

        $this->postJson('/api/payment-requests', ['amount_local' => 100])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Exchange rate provider is unavailable.');

        $this->assertDatabaseCount('payment_requests', 0);
    }

    public function test_listing_filter_and_show_respect_employee_scope(): void
    {
        $employee = $this->employee();
        $other = $this->employee(['email' => 'other@example.com']);
        $own = PaymentRequest::factory()->create(['user_id' => $employee->id, 'status' => PaymentRequestStatus::Pending]);
        PaymentRequest::factory()->create(['user_id' => $other->id, 'status' => PaymentRequestStatus::Pending]);
        Passport::actingAs($employee);

        $this->getJson('/api/payment-requests?status=pending')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson("/api/payment-requests/{$own->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $own->id);
    }

    public function test_finance_can_approve_and_reject_pending_requests(): void
    {
        $finance = $this->finance();
        $employee = $this->employee();
        $approve = PaymentRequest::factory()->create(['user_id' => $employee->id, 'status' => PaymentRequestStatus::Pending]);
        $reject = PaymentRequest::factory()->create(['user_id' => $employee->id, 'status' => PaymentRequestStatus::Pending]);
        Passport::actingAs($finance);

        $this->patchJson("/api/payment-requests/{$approve->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->patchJson("/api/payment-requests/{$reject->id}/reject", ['notes' => 'Missing invoice'])
            ->assertOk()
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.notes', 'Missing invoice');
    }

    public function test_employee_cannot_approve_and_final_requests_are_immutable(): void
    {
        $employee = $this->employee();
        $finance = $this->finance();
        $pending = PaymentRequest::factory()->create(['user_id' => $employee->id, 'status' => PaymentRequestStatus::Pending]);
        $approved = PaymentRequest::factory()->create(['status' => PaymentRequestStatus::Approved]);

        Passport::actingAs($employee);
        $this->patchJson("/api/payment-requests/{$pending->id}/approve")->assertForbidden();

        Passport::actingAs($finance);
        $this->patchJson("/api/payment-requests/{$approved->id}/approve")
            ->assertUnprocessable()
            ->assertJsonPath('errors.status.0', 'Only pending payment requests can be approved.');
    }

    public function test_expiration_command_expires_pending_requests_created_more_than_48_hours_ago(): void
    {
        PaymentRequest::factory()->create(['status' => PaymentRequestStatus::Pending, 'created_at' => now()->subHours(49)]);
        PaymentRequest::factory()->create(['status' => PaymentRequestStatus::Pending, 'created_at' => now()->subHours(2)]);

        $this->artisan('payments:expire')->assertSuccessful();

        $this->assertDatabaseHas('payment_requests', ['status' => 'expired']);
        $this->assertDatabaseHas('payment_requests', ['status' => 'pending']);
    }

    private function employee(array $attributes = []): User
    {
        return User::factory()->create(['role' => 'employee', 'country_code' => 'PT', 'currency_code' => 'EUR', ...$attributes]);
    }

    private function finance(array $attributes = []): User
    {
        return User::factory()->create(['role' => 'finance', 'country_code' => 'PT', 'currency_code' => 'EUR', ...$attributes]);
    }
}
