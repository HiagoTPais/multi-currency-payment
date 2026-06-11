<?php

use App\Http\Controllers\ApiDocumentationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');
Route::get('/dashboard', fn () => Inertia::render('Dashboard/Index'))->name('dashboard');
Route::get('/payment-requests', fn () => Inertia::render('PaymentRequests/Index'))->name('payment-requests.index');
Route::get('/payment-requests/create', fn () => Inertia::render('PaymentRequests/Create'))->name('payment-requests.create');
Route::get('/payment-requests/{id}', fn (string $id) => Inertia::render('PaymentRequests/Show', [
    'id' => $id,
]))->name('payment-requests.show');

Route::get('/api/documentation', [ApiDocumentationController::class, 'index'])->name('api.documentation');
Route::get('/api/documentation.json', [ApiDocumentationController::class, 'specification'])->name('api.documentation.json');
