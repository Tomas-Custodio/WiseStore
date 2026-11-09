<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::public.index')->name('home');

Route::middleware(['auth', 'can:only-admin'])->prefix('admins')->group(function () {

    Route::livewire('/', 'pages::dashboard')->name('dashboard');

    Route::livewire('/customers', 'pages::customer.index')->name('customers.index');

    Route::livewire('/team', 'pages::user.index')->name('users.index');
    Route::livewire('/team/create', 'pages::user.create')->name('users.create');
    Route::livewire('/team/{user}/edit', 'pages::user.edit')->name('users.edit');

    Route::livewire('/products', 'pages::product.index')->name('products.index');
    Route::livewire('/products/create', 'pages::product.create')->name('products.create');
    Route::livewire('/products/{product}/edit', 'pages::product.edit')->name('products.edit');

    Route::livewire('/comments', 'pages::comment.index')->name('comments.index');

    Route::livewire('/categories', 'pages::category.index')->name('categories.index');
    Route::livewire('/categories/create', 'pages::category.create')->name('categories.create');
    Route::livewire('/categories/{category}/edit', 'pages::category.edit')->name('categories.edit');

});

Route::prefix('public')->name('public.')->group(function () {

    Route::livewire('/login', 'pages::public.auth.login')->name('login');

    Route::post('/logout', function () {

        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('public.login');

    })->name('logout');

    Route::livewire('/register', 'pages::public.user.create')->name('users.create');
    Route::livewire('/profile', 'pages::public.user.edit')->middleware('auth')->name('profile');

    Route::livewire('/encomendas', 'pages::encomendas.index')->middleware('auth')->name('encomendas.index');

    Route::livewire('/products', 'pages::public.product.index')->name('products.index');
    Route::livewire('/products/{product}', 'pages::public.product.show')->name('products.show');
    Route::livewire('/cart', 'pages::cart.index')->middleware('auth')->name('cart.index');

    Route::livewire('/comments/{product}', 'pages::public.comment.index')->name('comments.index');

    Route::livewire('/categories', 'pages::public.category.index')->name('categories.index');
    Route::livewire('/categories/{category}', 'pages::public.category.show')->name('categories.show');

});
