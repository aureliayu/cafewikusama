<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
//  
Route::get('/', function () {
  view('welcome');
});
