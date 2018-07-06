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
Auth::routes();
Route::group(['prefix' => '/v1/project/{project}/channel/{channel}', 'middleware' => ['auth:api', 'project_role:admin']], function() {

});

Route::group(['prefix' => '/v1/project/{project}/crm', 'middleware' => ['auth:api', 'project_role:admin']], function() {

});

/**
 * MOBIMESH
 */
Route::group(['prefix' => '/v1/project/{project}/channel/{channel}/mobimesh'], function() {

});

/**
 * LOYALTIES
 */
Route::group(['prefix' => '/v1/project/{project}/loyalties', 'middleware' => ['auth:api', 'project_role:user']], function() {

    Route::get('/', 'ApiController\LoyaltyController@index');
    Route::post('/', 'ApiController\LoyaltyController@create');
    Route::put('/{loyalty}', 'ApiController\LoyaltyController@update');

    Route::get('/{loyalty}/dashboard/metrics', 'ApiController\LoyaltyController@dashboard_metrics');
    Route::get('/{loyalty}/dashboard/tops', 'ApiController\LoyaltyController@dashboard_tops');

    Route::get('/{loyalty}/leaderboard', 'ApiController\LoyaltyController@leaderboard');
    Route::post('/{loyalty}/leaderboard/search', 'ApiController\LoyaltyController@leaderboard_search');

    Route::get('/{loyalty}/leaderboard/member/{uuid}', 'ApiController\LoyaltyController@member');

    Route::put('/{loyalty}/leaderboard/member/{uuid}/address', 'ApiController\MemberController@save_address');
    Route::put('/{loyalty}/leaderboard/member/{uuid}/note', 'ApiController\MemberController@save_note');

    Route::post('/{loyalty}/channels/poynts', 'ApiController\LoyaltyController@add_poynt_channel');
    // Route::post('/{loyalty}/channels/facebook', 'ApiController\LoyaltyController@add_facebook_channel');

    Route::post('/members/register', 'ApiController\MemberController@register_notify');
    Route::put('/members/subscribe', 'ApiController\MemberController@subscribe_notify');

    Route::post('/member/{uuid}/transactions/{transacion_id}', 'ApiController\UserController@store_transaction');
});

Route::group(['prefix' => '/v1/loyalties/members', 'middleware' => ['auth:api']], function() {
    Route::post('/verify_pin', 'ApiController\MemberController@verify_pin');
    Route::post('/search', 'ApiController\MemberController@search');

    Route::put('/{uuid}/projects', 'ApiController\MemberController@subscribe')->middleware(['teia']);
    Route::post('/create', 'ApiController\MemberController@create')->middleware(['teia']);
    Route::post('/login', 'ApiController\MemberController@login')->middleware(['teia']);

    Route::get('/{uuid}/projects/merchants', 'ApiController\MemberController@project_with_merchant')->middleware(['teia']);
});


Route::group(['prefix' => '/v1/projects', 'middleware' => ['auth:api']], function() {
    Route::post('/', 'ApiController\ProjectController@create');
    Route::get('/channels/poynts', 'ApiController\ProjectController@find_by_business_and_device');
    Route::post('/{project}/channels/poynts', 'ApiController\ProjectController@create_poynt_channel');
});


Route::post('api/v1/project/{project}/channels/poynts', 'ApiController\ProjectController@create_poynt_channel')->middleware(['auth:api', 'project_role:owner']);

Route::group([ 'middleware' => 'api', 'prefix' => '/v1' ], function ($router) {
    Route::post('register', 'ApiController\UserController@register');
    Route::post('login', 'ApiController\UserController@login');
    Route::post('forget_password', 'ApiController\UserController@forgetpass');
    Route::put('update', 'ApiController\UserController@update')->middleware('auth:api');
    Route::put('skip_tutorial', 'ApiController\UserController@skip_tutorial')->middleware('auth:api');
    Route::get('transactions', 'ApiController\UserController@transactions')->middleware('auth:api') ;
    Route::delete('delete', 'ApiController\UserController@delete')->middleware('auth:api');
    Route::post('user/verify', 'ApiController\UserController@verify') ;
});


