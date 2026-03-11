<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmojiController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VoteSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // User profile
    Route::get('/profile', [UserController::class, 'show']);
    Route::post('/profile', [UserController::class, 'update']);

    // Rooms
    Route::get('/rooms', [RoomController::class, 'index']);
    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/rooms/{id}', [RoomController::class, 'show']);
    Route::post('/rooms/join/{code}', [RoomController::class, 'join']);
    Route::post('/rooms/{room}/leave', [RoomController::class, 'leave']);
    Route::patch('/rooms/{room}/state', [RoomController::class, 'updateState']);
    Route::patch('/rooms/{room}/toggle-emojis', [RoomController::class, 'toggleEmojis']);

    // Vote Sessions
    Route::post('/rooms/{room}/sessions', [VoteSessionController::class, 'store']);
    Route::get('/sessions/{voteSession}', [VoteSessionController::class, 'show']);
    Route::post('/sessions/{voteSession}/vote', [VoteSessionController::class, 'submitVote']);
    Route::post('/sessions/{voteSession}/reveal', [VoteSessionController::class, 'reveal']);

    // Emojis
    Route::post('/rooms/{room}/emojis', [EmojiController::class, 'send']);
});
