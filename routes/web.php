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
Route::get('/mytest', function() {     return "Oh yeah, this really works !"; }); 
Route::get('/', function () {
    return view('welcome');
});

Route::post('/server/deploy', 'FileUploaderController@deployPOC');
Route::post('/server/deploy/playercommonplugin', 'FileUploaderController@deployPlayerCommonPlugin');
Route::post('/server/deploy/playercommonpluginprod', 'FileUploaderController@deployPlayerCommonPluginProd');
Route::post('/server/deploy/playercommonvod', 'FileUploaderController@deployPlayerCommonVOD');
