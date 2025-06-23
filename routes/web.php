<?php

use Google\Client;
use Google\Service\Drive;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/oauth', function () {
    $client = new Client();
    $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
    $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
    $client->setRedirectUri(env('GOOGLE_DRIVE_REDIRECT_URI')); // Pastikan ini sama dengan yang di GC Console
    $client->addScope(Drive::DRIVE); // Scope untuk akses Google Drive
    $client->setAccessType('offline'); // Penting untuk mendapatkan refresh token
    $client->setPrompt('select_account consent'); // Penting untuk mendapatkan refresh token setiap kali

    if (!request()->has('code')) {
        // Redirect user to Google for authorization
        return redirect($client->createAuthUrl());
    } else {
        // Handle callback from Google
        $accessToken = $client->fetchAccessTokenWithAuthCode(request('code'));
        // Simpan refresh_token ini! Ini yang kamu butuhkan.
        dd($accessToken['refresh_token']);
        // Setelah mendapatkan refresh_token, hapus route ini
    }
});
