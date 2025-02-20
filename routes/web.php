<?php

use App\Http\Controllers\AuthManager;
use App\Http\Controllers\OrderManager;
use App\Http\Controllers\ProductManager;
use App\Http\Middleware\RoleAdmin;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('Hi');
});
Route::get('dashboard',function(){
    return "dashboard";
})->name('dashboard');
Route::get('login',[AuthManager::class,'login'])->name('login');
Route::get('logout',[AuthManager::class,'logout'])->name('logout');
Route::post('login',[AuthManager::class,'loginPost'])->name('login.post');
Route::prefix('admin')->middleware(RoleAdmin::class)->group(function(){
    Route::get('dashboard',[OrderManager::class,'newOrders'])->name('dashboard');
    Route::get('products',[ProductManager::class,'listProducts'])->name('products');
    Route::post('products',[ProductManager::class,'addProducts'])->name('product.add');
    Route::get('product/delete',[ProductManager::class,'deleteProducts'])->name('product.delete');
    Route::post('order/assign',[OrderManager::class,'assignOrder'])->name('order.assign');
    Route::get('order/list',[OrderManager::class,'listOrders'])->name('order.list');
});