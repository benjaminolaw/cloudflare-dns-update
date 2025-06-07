<?php

use App\Http\Controllers\CloudflareController;
use Illuminate\Support\Facades\Route;

Route::get('/', CloudflareController::class.'@test');
