<?php

use App\Http\Controllers\AddressBookController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\BalanceTypeController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\PasswordRecoveryCheckToken;
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
*/

Route::get('/basicData', [BalanceTypeController::class, 'getBasicData']);

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/logout', [UserController::class, 'login']);
Route::post('/askPasswordRecover', [UserController::class, 'askPasswordRecovery']);
Route::post('/resetPassword', [UserController::class, 'resetPassword'])->middleware(PasswordRecoveryCheckToken::class);

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/update', [UserController::class, 'update']);
    Route::get('/balance', [BalanceController::class, 'getBalance']);
    Route::get('/transactions', [TransactionController::class, 'getTransactions']);
    Route::post('/transaction', [TransactionController::class, 'makeTransaction']);
    Route::post('/active', [BalanceController::class, 'insertActive']);
    Route::post('/pasive', [BalanceController::class, 'insertPassive']);
    Route::get('/contacts', [AddressBookController::class, 'getContacts']);
    Route::post('/contact', [AddressBookController::class, 'addContactToUser']);
});


