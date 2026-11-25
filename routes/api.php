<?php

use App\Http\Controllers\Api\V1\PlatformApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/institutions', [PlatformApiController::class, 'institutions']);
    Route::get('/products', [PlatformApiController::class, 'products']);
    Route::get('/campaigns', [PlatformApiController::class, 'campaigns']);
    Route::get('/orders/{orderNumber}/track', [PlatformApiController::class, 'trackOrder']);
});
