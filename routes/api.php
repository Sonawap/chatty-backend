<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\API\ChatController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\GroupController;
use App\Http\Controllers\API\GroupMemberController;
use App\Http\Controllers\API\GroupMessageController;
use App\Http\Controllers\API\DirectMessageController;

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
Route::post('/validate', [UserController::class, 'validateEmail']);

Route::group(['prefix' => 'user'], function() {
    Route::post('create', [UserController::class, 'store']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('forgot', [UserController::class, 'forgot']);
    Route::post('password/change', [UserController::class, 'password']);
});


Route::group(['middleware' => 'auth:sanctum'], function() {

    Broadcast::routes();

    Route::get('/user', [UserController::class, 'user']);
    Route::get('/users', [UserController::class, 'allUsers']);

    Route::group(['prefix' => 'user'], function() {

        Route::post('logout', [UserController::class, 'logout']);
        Route::get('groups', [UserController::class, 'groups']);
        Route::get('belongToGroups', [UserController::class, 'belongToGroups']);
        Route::post('uploadProfilePhoto', [UserController::class, 'UploadImage']);
        Route::get('allChats', [ChatController::class, 'allChats']);

    });

    Route::apiResource('groups', GroupController::class);

    Route::group(['prefix' => 'group'], function() {
        Route::get('{group_id}', [GroupMemberController::class, 'index']);
        Route::post('/', [GroupMemberController::class, 'add']);
        Route::post('/bulk', [GroupMemberController::class, 'addBulk']);
        Route::delete('/', [GroupMemberController::class, 'remove']);
    });

    Route::group(['prefix' => 'message'], function() {
        Route::get('{group_id}', [GroupMessageController::class, 'index']);
        Route::post('/', [GroupMessageController::class, 'send']);
        Route::post('/direct', [DirectMessageController::class, 'send']);
    });


    Route::group(['prefix' => 'chat'], function() {
        Route::get('group', [ChatController::class, 'group']);
        Route::post('direct', [ChatController::class, 'direct']);
    });

});
