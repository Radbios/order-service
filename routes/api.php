<?php

use App\Auth;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware("api_auth")->group(function() {
    Route::get("test", function(Request $request) {
        return Auth::user()->id;
    });
});
