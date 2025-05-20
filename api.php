<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\tableController;
use App\Http\Controllers\transaksiController;
use App\Http\Controllers\detailTransaksiController;
use App\Http\Controllers\userController;
use App\Http\Controllers\pdfController;

//API untuk auth logout
Route::middleware('auth:user_model')->group(function () {

    Route::controller(AuthController::class)->group(function (){

        Route::post('/logout', 'logout');

    });

    //Admin API
    Route::controller(FoodController::class)->group(function (){

        Route::get('/admin/food/get', 'getFood');
        Route::get('/admin/food/getId/{id}', 'getFoodId');
        Route::post('/admin/food/add', 'addFood');
        Route::post('/admin/food/update/{id}', 'updateFood');
        Route::delete('/admin/food/delete/{id}', 'deleteFood');

    });

    Route::controller(tableController::class)->group(function () {

        Route::get('/admin/meja/get', 'getMeja');
        Route::get('/admin/meja/getId/{id}', 'getMejaId');
        Route::post('/admin/meja/add', 'addMeja');
        Route::post('/admin/meja/update/{id}', 'updateMeja');
        Route::delete('/admin/meja/delete/{id}', 'deleteMeja');

    });

    Route::controller(userController::class)->group(function () {
        
        Route::get('/admin/user/get', 'getUserData'); 
        Route::get('/admin/user/getId/{id}', 'getUserDataId'); 
        Route::post('/admin/user/update/{id}', 'updateUserData');
        Route::delete('/admin/user/delete/{id}', 'deleteUser');
        
    });

    //Kasir API
    Route::controller(transaksiController::class)->group(function () {
       
        Route::get('/kasir/transaksi/get', 'getTransaksiPerUser');
        Route::get('/kasir/transaksi/getId/{id}', 'getTransaksiId');
        Route::post('/kasir/transaksi/add', 'addTransaksi');
        Route::patch('/kasir/transaksi/update/{id}', 'updateTransaksi');
        Route::delete('/kasir/transaksi/delete/{id}', 'deleteTransaksi');

    });

    Route::controller(detailTransaksiController::class)->group(function (){

        Route::get('/kasir/transaksi/detail/getAll', 'getAll');
        Route::get('/kasir/transaksi/detail/getId/{id}', 'getDetailId');
        Route::get('/kasir/transaksi/detail/DetailTransaksiId/{id}', 'getDetailTransaksiId');
        Route::post('/kasir/transaksi/detail/add/{id}', 'addDetailTransaksi');
        Route::patch('/kasir/transaksi/detail/update/{id}', 'updateDetailTransaksi');
        Route::delete('/kasir/transaksi/detail/delete/{id}', 'deleteDetailTransaksi');

    });

    Route::controller(TableController::class)->group(function (){

        Route::get('/kasir/meja/get', 'getMeja');
        Route::get('/kasir/meja/getKosong', 'getMejaKosong');
        Route::get('/kasir/meja/getId/{id}', 'getMejaId');
        Route::patch('/kasir/meja/update/{id}', 'updateMeja');
        
    });

    Route::controller(FoodController::class)->group(function (){

        Route::get('/kasir/food/get', 'getFood');
        Route::get('/kasir/food/getId/{id}', 'getFoodId');
        
    });

    Route::controller(pdfController::class)->group(function (){

        Route::get('/kasir/pdf/getPdf/{id}', 'getPdf');
        
    });

    //Manajer API
    Route::controller(transaksiController::class)->group(function () {
       
        Route::get('/manajer/transaksi/get', 'getTransaksi');
        Route::get('/manajer/transaksi/getId/{id}', 'getTransaksiId');
        Route::get('/manajer/transaksi/lunasId/{id}', 'pembayaranlunasId');
        Route::get('/manajer/transaksi/blmbyrId/{id}', 'belumbayarId');
       

    });

    Route::controller(detailTransaksiController::class)->group(function (){

        Route::get('/manajer/transaksi/detail/DetailTransaksiId/{id}', 'getDetailTransaksiId');
        Route::get('/manajer/transaksi/belumbayar','getblmbyr');
        Route::get('/manajer/transaksi/lunas','getlunas');
    });
    
});

Route::controller(AuthController::class)->group(function (){

    Route::post('/register', 'register');
    Route::post('/login', 'login');

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:user_model');