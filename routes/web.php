<?php

use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});