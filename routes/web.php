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

Auth::routes();
Route::middleware('auth:web')->group(static function(){
     Route::redirect('/', '/login');
     Route::get('/home','HomeController@home');
    
    // logout 被使用了,路由缓存会报错，所以重命名
    Route::get('game/logout','Auth\LoginController@logout')->name('game-logout');
    Route::get('/user/getBalance','HomeController@getBalance');
    Route::get('/user/checkComBank','HomeController@checkComBank');
    Route::get('/play','HomeController@play')->name('play');
    Route::get('/deposit','HomeController@deposit')->name('deposit');
    Route::post('/depositSb','HomeController@depositSb')->name('depositSb');
    Route::get('/dw-list','HomeController@dwList')->name('dw-list');
    Route::get('/bonus-list','HomeController@bsList')->name('bonus-list');
    Route::get('/person', 'HomeController@person')->name('person');
    Route::get('/refList', 'HomeController@refList')->name('refList');
    Route::get('/reportList', 'HomeController@reportList')->name('reportList');
    Route::get('/withdraw','HomeController@withdraw')->name('withdraw');
    Route::post('/withdrawSb','HomeController@withdrawSb')->name('withdrawSb');
    Route::get('/msglist','NoticeController@msgLsit')->name('msglist');
});
Route::get('/index', 'HomeController@index')->name('index');
Route::get('/gamerule', 'HomeController@rule')->name('gamerule');
Route::get('/game','HomeController@game')->name('game');
Route::get('/bonus','HomeController@bonus')->name('bonus');
Route::get('/contact','HomeController@contact')->name('contact');
Route::get('/listJion','HomeController@listJion')->name('listJion');
Route::get('/jionSend','HomeController@jionSend')->name('jionSend');
Route::get('/user/checkUser','HomeController@checkUser');
Route::get('/user/checkBank','HomeController@checkBank');
Route::get('/slot','HomeController@slot')->name('slot');
Route::get('/hbslot','HomeController@hbslot')->name('hbslot');
Route::get('/pgslot','HomeController@pgslot')->name('pgslot');
Route::get('/goSlot','HomeController@goSlot')->name('goSlot');
Route::get('/sports','HomeController@sports')->name('sports');
Route::get('/afbsport','HomeController@afbsport')->name('afbsport');
Route::get('/sabungAyam','HomeController@sabungAyam')->name('sabungAyam');
Route::get('/poker','HomeController@poker')->name('poker');
Route::get('/user/checkCaptcha','HomeController@checkCaptcha');
Route::get('/getCustomer','HomeController@getCustomer');

// rdp777 route

Route::get('/rdpindex', 'HomeController@rdpindex')->name('rdpindex');



Route::post('/pp/authenticate','PplayController@authenticate');
Route::post('/pp/balance','PplayController@balance');
Route::post('/pp/bet','PplayController@bet');
Route::post('/pp/result','PplayController@result');
Route::post('/pp/refund','PplayController@refund');
Route::post('/pp/bonusWin','PplayController@bonusWin');
Route::post('/pp/jackpotWin','PplayController@jackpotWin');
Route::post('/pp/promoWin','PplayController@promoWin');
Route::post('/pp/endround','PplayController@endround');
Route::post('/getGameResult','PplayController@getGameResult')->name('getGameResult');
Route::get('/getGameUrl','PplayController@getGameUrl')->name('getGameUrl');
//Route::get('/pp/getGameList','PplayController@getGameList');
Route::post('/hb/user','HbController@authenticate');
Route::post('/hb/bet','HbController@bet');
Route::get('/getHbResult','HbController@getHbResult')->name('getHbResult');
Route::post('/pg/user','PgController@authenticate');
Route::post('/pg/bet','PgController@bet');
Route::post('/pg/balance','PgController@balance');
Route::get('/getPgResult','PgController@getPgResult')->name('getPgResult');
//afb调用接口
Route::post('/afb/balance','AfbController@balance');
Route::post('/afb/bet','AfbController@bet');
Route::post('/afb/result','AfbController@result');
Route::post('/afb/refund','AfbController@refund');
Route::get('/getAfbResult','AfbController@getAfbResult')->name('getAfbResult');
//主页新增接口
Route::get('/getTransferShow','HomeController@getTransferShow')->name('getTransferShow');
Route::get('/getSLotWin','HomeController@getSLotWin')->name('getSLotWin');
