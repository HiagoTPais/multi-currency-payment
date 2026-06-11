<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ensurePassportKeysExist();
        $this->createPassportPersonalAccessClient();
    }

    public function test_user_can_register(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Ana Portugal',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
            'country_code' => 'pt',
            'currency_code' => 'eur',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'User registered successfully.')
            ->assertJsonPath('user.currency_code', 'EUR');
    }

    public function test_user_can_login_access_me_and_logout_revoke_token(): void
    {
        User::factory()->create([
            'email' => 'finance@example.com',
            'password' => 'password',
            'role' => 'finance',
            'country_code' => 'PT',
            'currency_code' => 'EUR',
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'finance@example.com',
            'password' => 'password',
        ])
            ->assertOk()
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonStructure(['access_token', 'token_type', 'expires_at', 'user'])
            ->json('access_token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'finance@example.com');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Access token revoked successfully.');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_invalid_login_and_missing_token_are_rejected(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'missing@example.com',
            'password' => 'wrong-password',
        ])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials.');

        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    private function createPassportPersonalAccessClient(): void
    {
        app(ClientRepository::class)->createPersonalAccessGrantClient('Test Personal Access Client', 'users');
    }

    private function ensurePassportKeysExist(): void
    {
        $privateKeyPath = storage_path('oauth-private.key');
        $publicKeyPath = storage_path('oauth-public.key');

        if (file_exists($privateKeyPath) && file_exists($publicKeyPath)) {
            return;
        }

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);
        $publicKey = openssl_pkey_get_details($key)['key'];

        file_put_contents($privateKeyPath, $privateKey);
        file_put_contents($publicKeyPath, $publicKey);
    }
}
