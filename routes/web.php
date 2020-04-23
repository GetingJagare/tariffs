<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {

    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('system');
    }

    return view('welcome');
})->middleware(['auth']);

Route::get('/system', 'SystemController@index')->middleware(['auth'])->name('system');
Route::get('/tariffs', 'SystemController@getTariffs');
Route::post('/import-tariffs', 'SystemController@importTariffs')->middleware(['ajax']);
Route::get('/export', 'SystemController@exportTariffs')->middleware(['ajax']);
Route::get('/check-feed', 'SystemController@checkFeed')->middleware(['ajax']);

Route::get('/login', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {

        return redirect('system');

    }

    return view('login');
})->name('login');

Route::post('/login', 'MainController@login')->middleware(['ajax']);
Route::post('/logout', 'MainController@logout')->middleware(['ajax']);
