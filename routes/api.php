<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => ['api'],
    'prefix'     => 'auth'
], function ($router) {
    Route::get('{service}/new', 'AuthController@new');
    Route::get('{service}/login', 'AuthController@login');

    Route::get('callback', 'AuthController@callback');

    Route::group(['middleware' => ['jwt.auth']], function () {
        Route::get('{service}/add', 'AuthController@add');
        Route::post('refresh', 'AuthController@refresh');
        Route::post('logout', 'AuthController@logout');
        Route::get('me', 'AuthController@me');
    });
});
