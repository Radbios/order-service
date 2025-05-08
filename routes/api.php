<?php

use App\Auth;
use App\Http\Controllers\OrderController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware("api_auth")->group(function() {
    Route::get("order", [OrderController::class, "index"]);
    Route::get("order/my-top-category", [OrderController::class, "my_top_category"]);
    Route::get("order/top-category", [OrderController::class, "top_category"]);
    Route::post("order", [OrderController::class, "store"]);
    Route::get("order/{order}", [OrderController::class, "show"]);
    Route::put("order/{order}", [OrderController::class, "update"]);
    
    Route::get("test", function(Request $request) {
        return Auth::user()->id;
    });
});
