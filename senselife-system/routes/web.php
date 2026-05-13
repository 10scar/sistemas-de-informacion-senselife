<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/livewire-demo', function () {
    return view('livewire-demo');
});
