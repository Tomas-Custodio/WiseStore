<?php

use App\Models\PaymentProof;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::livewire('/', 'pages::public.index')->name('home');

Route::middleware(['auth', 'can:only-admin'])->prefix('admins')->group(function () {

    Route::livewire('/', 'pages::dashboard')->name('dashboard');

    Route::livewire('/customers', 'pages::admin.customer.index')->name('customers.index');

    Route::livewire('/team', 'pages::admin.user.index')->name('users.index');
    Route::livewire('/team/create', 'pages::admin.user.create')->name('users.create');
    Route::livewire('/team/{user}/edit', 'pages::admin.user.edit')->name('users.edit');

    Route::livewire('/products', 'pages::admin.product.index')->name('products.index');
    Route::livewire('/products/create', 'pages::admin.product.create')->name('products.create');
    Route::livewire('/products/{product}/edit', 'pages::admin.product.edit')->name('products.edit');

    Route::livewire('/comments', 'pages::admin.comment.index')->name('comments.index');
    Route::livewire('/categories', 'pages::admin.category.index')->name('categories.index');
    Route::livewire('/categories/create', 'pages::admin.category.create')->name('categories.create');
    Route::livewire('/categories/{category}/edit', 'pages::admin.category.edit')->name('categories.edit');

    Route::livewire('/bank', 'pages::admin.bank.index')->name('bank.index');
    Route::livewire('/bank/create', 'pages::admin.bank.create')->name('bank.create');
    Route::livewire('/bank/{banco}/edit', 'pages::admin.bank.edit')->name('bank.edit');

    Route::livewire('/orders', 'pages::admin.order.index')->name('orders.index');

    Route::get('/payment-proofs/{paymentProof}', function (PaymentProof $paymentProof) {

        abort_unless(Storage::disk('public')->exists($paymentProof->doc), 404);

        // Mostra o ficheiro no navegador (inline) em vez de o descarregar
        return Storage::disk('public')->response($paymentProof->doc);

    })->name('payment-proofs.show');

});

Route::name('public.')->group(function () {

    Route::livewire('/login', 'pages::public.auth.login')->middleware('guest')->name('login');

    Route::post('/logout', function () {

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('public.login');

    })->name('logout');

    Route::livewire('/register', 'pages::public.user.create')->middleware('guest')->name('users.create');
    Route::livewire('/profile', 'pages::public.user.edit')->middleware('auth')->name('profile');

    Route::livewire('/encomendas', 'pages::public.order.index')->middleware('auth')->name('encomendas.index');
    Route::livewire('/pagamentos/{order}', 'pages::public.payment.index')->middleware('auth')->name('payment.index');

    Route::livewire('/checkout', 'pages::public.checkout')->middleware('auth')->name('checkout');

    Route::livewire('/promocoes', 'pages::public.promotion.index')->name('promotions.index');

    Route::livewire('/products', 'pages::public.product.index')->name('products.index');
    Route::livewire('/products/{product}', 'pages::public.product.show')->name('products.show');
    Route::livewire('/cart', 'pages::cart.index')->middleware('auth')->name('cart.index');

    Route::livewire('/comments/{product}', 'pages::public.comment.index')->name('comments.index');

    Route::livewire('/categories', 'pages::public.category.index')->name('categories.index');
    Route::livewire('/categories/{category}', 'pages::public.category.show')->name('categories.show');

});
