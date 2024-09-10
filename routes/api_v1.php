<?php

use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->apiResource('tickets', TicketController::class)->except('update');
Route::middleware('auth:sanctum')->put('tickets/{ticket}', [TicketController::class, 'replace'])->name('tickets.replace');
Route::middleware('auth:sanctum')->patch('tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');

Route::middleware('auth:sanctum')->apiResource('users', UserController::class);
