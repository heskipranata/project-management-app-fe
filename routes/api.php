<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'update']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('projects', [ProjectController::class, 'mine']);
    Route::post('projects', [ProjectController::class, 'store']);
    Route::get('projects/{project}', [ProjectController::class, 'show']);
    Route::put('projects/{project}', [ProjectController::class, 'update']);
    Route::patch('projects/{project}', [ProjectController::class, 'update']);
    Route::delete('projects/{project}', [ProjectController::class, 'destroy']);

    Route::get('tasks', [TaskController::class, 'mine']);
    Route::post('tasks', [TaskController::class, 'store']);
    Route::get('tasks/{task}', [TaskController::class, 'show']);
    Route::put('tasks/{task}', [TaskController::class, 'update']);
    Route::patch('tasks/{task}', [TaskController::class, 'update']);
    Route::delete('tasks/{task}', [TaskController::class, 'destroy']);

});
