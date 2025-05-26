<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\CommentController;

// Get authenticated user
Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'User retrieved successfully.',
        'data' => new UserResource($request->user())
    ]);
})->middleware('auth:sanctum');

// Protected Routes (Require Authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Authentication Routes
    Route::post('/auth/logout', [LogoutController::class, 'logout']);

    // Task Management Routes
    Route::apiResource('tasks', TaskController::class);
    Route::get('/users/{user}/tasks', [TaskController::class, 'userTasks']);

    // Comment Management Routes
    Route::apiResource('comments', CommentController::class)->except(['index']);
    Route::get('/tasks/{task}/comments', [CommentController::class, 'index']);
    Route::get('/users/{user}/comments', [CommentController::class, 'userComments']);
});

// Public Authentication Routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/register', [RegisterController::class, 'register']);
});
