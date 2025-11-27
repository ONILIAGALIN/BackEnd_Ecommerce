<?php

use Illuminate\Support\Facades\Route;

Route::post("/register", [App\Http\Controllers\AuthController::class, "register"]);
Route::post("/login", [App\Http\Controllers\AuthController::class, "login"]);
Route::middleware("auth:api")->get("/checkToken", [App\Http\Controllers\AuthController::class, "checkToken"]);

Route::prefix("users")->group(function () {
    Route::post("/", [App\Http\Controllers\UserController::class, "store"]);
    Route::get("/", [App\Http\Controllers\UserController::class, "index"]);
    Route::get("/{user}", [App\Http\Controllers\UserController::class, "show"]);
    Route::patch("/{user}", [App\Http\Controllers\UserController::class, "update"]);
    Route::delete("/{user}", [App\Http\Controllers\UserController::class, "destroy"]);
});

Route::prefix("products")->group(function () {
    Route::post("/", [App\Http\Controllers\ProductController::class, "store"]);
    Route::get("/", [App\Http\Controllers\ProductController::class, "index"]);
    Route::get("/{product}", [App\Http\Controllers\ProductController::class, "show"]);
    Route::patch("/{product}", [App\Http\Controllers\ProductController::class, "update"]);
    Route::delete("/{product}", [App\Http\Controllers\ProductController::class, "destroy"]);
});

Route::middleware('auth:api')->prefix("carts")->group(function () {
    Route::post("/", [App\Http\Controllers\CartController::class, "store"]);
    Route::get("/", [App\Http\Controllers\CartController::class, "index"]);
    Route::get("/{cart}", [App\Http\Controllers\CartController::class, "show"]);
    Route::patch("/{cart}", [App\Http\Controllers\CartController::class, "update"]);
    Route::delete("/{cart}", [App\Http\Controllers\CartController::class, "destroy"]);
});


Route::middleware('auth:api')->prefix("orders")->group(function () {
    Route::post("/", [App\Http\Controllers\OrderController::class, "checkout"]);
    Route::get("/", [App\Http\Controllers\OrderController::class, "index"]);
    Route::get("/{order}", [App\Http\Controllers\OrderController::class, "show"]);
    Route::patch("/{order}", [App\Http\Controllers\OrderController::class, "update"]);
    Route::delete("/{order}", [App\Http\Controllers\OrderController::class, "destroy"]);
});
