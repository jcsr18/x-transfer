<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'App\Http\Controllers\Api', 'as' => 'api.'], function () {
    Route::group(['prefix' => 'transfer', 'as' => 'transfer.'], function () {
        Route::post('', 'TransferController@store')->name('store');
    });
});
