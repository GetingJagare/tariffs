<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('system');
    }

    return view('welcome');
})->middleware(['auth']);

Route::get('/system', 'SystemController@index')->middleware(['auth'])->name('system');

Route::middleware(['ajax'])->group(function () {
    Route::post('/import-tariffs', 'SystemController@importTariffs');
    Route::post('/delete-tariff', 'SystemController@deleteTariff');
    Route::post('/save-tariff', 'SystemController@saveTariff');
    Route::get('/export', 'SystemController@exportTariffs');
    Route::get('/check-feed', 'SystemController@checkFeed');

    Route::post('/login', 'MainController@login');
    Route::post('/logout', 'MainController@logout');

    Route::post('/add-field', 'FieldController@addField');

    Route::get('/get-fields', 'FieldController@getFields');
    Route::get('/get-field-types', 'FieldController@getFieldTypes');
    Route::get('/tariffs', 'SystemController@getTariffs');

    Route::post('/add-field', 'FieldController@addField');
    Route::post('/delete-field-value', 'FieldController@deleteFieldValue');
    Route::post('/add-field-value', 'FieldController@addFieldValue');
});


Route::get('/login', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {

        return redirect('system');

    }

    return view('login');
})->name('login');
