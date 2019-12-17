<?php
Route::prefix('v1')->group(function () {
    Route::get('/', function () {
        echo "salam";
    });

    Route::middleware(['auth:api'])->group(function () {

    });
});
