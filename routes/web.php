<?php

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
    return view('welcome');
});
Route::post('/test', 'FileUploaderController@testDeploy');

Route::group(['prefix' => 'server/deploy'], function () {
    Route::post('playercommonplugin', 'FileUploaderController@deployPlayerCommonPluginStage');
	Route::post('playercommonpluginprod', 'FileUploaderController@deployPlayerCommonPluginProd');
	Route::post('playercommonvod', 'FileUploaderController@deployPlayerCommonVOD');
	Route::post('playercommonvodprod', 'FileUploaderController@deployPlayerCommonVODProd');

});
