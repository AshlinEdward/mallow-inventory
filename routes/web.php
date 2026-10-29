
<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/inventory', function () {
    return view('inventory');
});

Route::get('/orders', function () {
    return view('orders');
});

Route::get('/customers', function () {
    return view('customers');
});
