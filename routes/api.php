<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// User info (залишаємо як було)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// CRUD для постів
Route::get('/blog/posts', [\App\Http\Controllers\Api\Blog\PostController::class, 'index']);
Route::get('/blog/posts/{id}', [\App\Http\Controllers\Api\Blog\PostController::class, 'show']);
Route::post('/blog/posts', [\App\Http\Controllers\Api\Blog\PostController::class, 'store']);
Route::patch('/blog/posts/{id}', [\App\Http\Controllers\Api\Blog\PostController::class, 'update']);
Route::put('/blog/posts/{id}', [\App\Http\Controllers\Api\Blog\PostController::class, 'update']);
Route::delete('/blog/posts/{id}', [\App\Http\Controllers\Api\Blog\PostController::class, 'destroy']);

// CRUD для категорій
Route::get('/blog/categories', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'index']);
Route::get('/blog/categories/{id}', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'show']);
Route::post('/blog/categories', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'store']);
Route::patch('/blog/categories/{id}', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'update']);
Route::put('/blog/categories/{id}', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'update']);
Route::delete('/blog/categories/{id}', [\App\Http\Controllers\Api\Blog\CategoryController::class, 'destroy']);
