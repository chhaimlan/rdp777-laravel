<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
Route::middleware('auth:api')->get('/user', 'UserController@getUserInfo');
*/

Route::middleware('auth:api')->get('/user', 'UserController@getUserInfo');
Route::post('login','UserController@login');
Route::get('report','UserController@getReports')->name('report');
Route::post('password','UserController@resetPassword')->name('password');
Route::post('getBalance','UserController@getBalance')->name('getBalance');
Route::post('up_password','UserController@upPassword')->name('up_password');
Route::post('up_userInfo','UserController@updateUserInfo')->name('up_userInfo');
Route::post('get_user_limit','UserController@getUserLimit')->name('get_user_limit');
Route::get('newList','NoticeController@newList')->name('newList');
Route::post('msgList','NoticeController@getMsg')->name('msgList');
Route::get('getBankList','UserController@getBankList')->name('getBankList');
Route::post('checkUsername','UserController@checkUsername')->name('checkUsername');
Route::post('checkBanker','UserController@checkBanker')->name('checkBanker');
Route::post('checkRefCode','UserController@checkRefCode')->name('checkRefCode');
Route::post('checkCaptcha','UserController@checkCaptcha')->name('checkCaptcha');
Route::get('getCaptcha','UserController@getCaptcha')->name('getCaptcha');
Route::post('appReg','UserController@appReg')->name('appReg');
Route::post('getRefList','UserController@getRefList')->name('getRefList');
Route::post('getWithDraw','UserController@getWithDraw')->name('getWithDraw');
Route::post('setWithDraw','UserController@setWithDraw')->name('setWithDraw');
Route::post('getDeposit','UserController@getDeposit')->name('getDeposit');
Route::post('setDeposit','UserController@setDeposit')->name('setDeposit');
Route::post('getDwlist','UserController@getDwlist')->name('getDwlist');
Route::post('getReportlist','UserController@getReportlist')->name('getReportlist');
Route::post('getSlot','UserController@getSlot')->name('getSlot');
Route::post('getResult','UserController@getGameResult')->name('getResult');
Route::post('getPpurl','UserController@getPpurl')->name('getPpurl');
Route::post('getCurl','UserController@getCurl')->name('getCurl');
Route::post('getHb','UserController@getHb')->name('getHb');
Route::post('getHburl','UserController@getHburl')->name('getHburl');
Route::post('getHbResultPhone','UserController@getHbResultPhone')->name('getHbResultPhone');
Route::post('getPgUrl','UserController@getPgUrl')->name('getPgUrl');
Route::post('getPgResultPhone','UserController@getPgResultPhone')->name('getPgResultPhone');
Route::post('getAfbPhone','UserController@getAfbPhone')->name('getAfbPhone');
Route::post('getAfbPhoneResult','UserController@getAfbPhoneResult')->name('getAfbPhoneResult');
