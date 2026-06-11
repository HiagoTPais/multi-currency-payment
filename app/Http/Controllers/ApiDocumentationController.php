<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiDocumentationController extends Controller
{
    public function index(): View
    {
        return view('swagger');
    }

    public function specification(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Multi-Currency Payment API',
                'version' => '1.0.0',
                'description' => 'Laravel Passport protected API for multi-currency payment requests.',
            ],
            'servers' => [['url' => url('/api')]],
            'components' => [
                'securitySchemes' => [
                    'passportBearer' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'OAuth2 Access Token',
                    ],
                ],
                'schemas' => [
                    'User' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string', 'format' => 'uuid'],
                            'name' => ['type' => 'string'],
                            'email' => ['type' => 'string'],
                            'role' => ['type' => 'string', 'enum' => ['employee', 'finance']],
                            'country_code' => ['type' => 'string'],
                            'currency_code' => ['type' => 'string'],
                        ],
                    ],
                    'PaymentRequest' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'string', 'format' => 'uuid'],
                            'amount_local' => ['type' => 'number'],
                            'currency_code' => ['type' => 'string'],
                            'exchange_rate' => ['type' => 'number'],
                            'exchange_rate_source' => ['type' => 'string'],
                            'exchange_rate_timestamp' => ['type' => 'string', 'format' => 'date-time'],
                            'amount_eur' => ['type' => 'number'],
                            'status' => ['type' => 'string', 'enum' => ['pending', 'approved', 'rejected', 'expired']],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/auth/register' => [
                    'post' => [
                        'summary' => 'Register a user.',
                        'requestBody' => $this->jsonBody([
                            'name' => 'Ana Portugal',
                            'email' => 'ana@example.com',
                            'password' => 'password',
                            'password_confirmation' => 'password',
                            'role' => 'employee',
                            'country_code' => 'PT',
                            'currency_code' => 'EUR',
                        ]),
                        'responses' => ['201' => ['description' => 'Registered']],
                    ],
                ],
                '/auth/login' => [
                    'post' => [
                        'summary' => 'Login and receive a Laravel Passport access token.',
                        'requestBody' => $this->jsonBody(['email' => 'finance@example.com', 'password' => 'password']),
                        'responses' => ['200' => ['description' => 'Authenticated'], '401' => ['description' => 'Invalid credentials']],
                    ],
                ],
                '/auth/logout' => $this->securedPost('Revoke the current access token.'),
                '/auth/me' => [
                    'get' => [
                        'summary' => 'Return the authenticated user.',
                        'security' => [['passportBearer' => []]],
                        'responses' => ['200' => ['description' => 'Authenticated user']],
                    ],
                ],
                '/payment-requests' => [
                    'get' => [
                        'summary' => 'List payment requests.',
                        'security' => [['passportBearer' => []]],
                        'parameters' => [[
                            'name' => 'status',
                            'in' => 'query',
                            'schema' => ['type' => 'string', 'enum' => ['pending', 'approved', 'rejected', 'expired']],
                        ]],
                        'responses' => ['200' => ['description' => 'Paginated payment requests']],
                    ],
                    'post' => [
                        'summary' => 'Create a payment request.',
                        'security' => [['passportBearer' => []]],
                        'requestBody' => $this->jsonBody(['amount_local' => 1000, 'notes' => 'Travel reimbursement']),
                        'responses' => ['201' => ['description' => 'Created'], '503' => ['description' => 'Exchange provider unavailable']],
                    ],
                ],
                '/payment-requests/{id}' => $this->securedGet('Show a payment request.'),
                '/payment-requests/{id}/approve' => $this->securedPatch('Approve a pending payment request.'),
                '/payment-requests/{id}/reject' => $this->securedPatch('Reject a pending payment request.'),
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $example
     * @return array<string, mixed>
     */
    private function jsonBody(array $example): array
    {
        return ['required' => true, 'content' => ['application/json' => ['example' => $example]]];
    }

    /**
     * @return array<string, mixed>
     */
    private function securedPost(string $summary): array
    {
        return ['post' => ['summary' => $summary, 'security' => [['passportBearer' => []]], 'responses' => ['200' => ['description' => 'Success']]]];
    }

    /**
     * @return array<string, mixed>
     */
    private function securedGet(string $summary): array
    {
        return ['get' => ['summary' => $summary, 'security' => [['passportBearer' => []]], 'responses' => ['200' => ['description' => 'Success']]]];
    }

    /**
     * @return array<string, mixed>
     */
    private function securedPatch(string $summary): array
    {
        return ['patch' => ['summary' => $summary, 'security' => [['passportBearer' => []]], 'responses' => ['200' => ['description' => 'Success']]]];
    }
}
