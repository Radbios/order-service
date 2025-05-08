<?php

use App\Auth;
use App\Http\Controllers\OrderController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware("api_auth")->group(function() {
    Route::post("order", [OrderController::class, "store"]);
    
    Route::get("test", function(Request $request) {
        return Auth::user()->id;
    });
});
