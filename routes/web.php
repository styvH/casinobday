<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Livewire\HomePage;

Route::get('/', HomePage::class)->name('home');

use App\Livewire\DashboardJoueur;
use App\Livewire\BetMaster;
use App\Livewire\Auth\CheckIn;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Auth;

Route::get('/check-in', CheckIn::class)->name('login');
Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

Route::middleware(['auth'])->group(function(){
	Route::get('/player/dashboard', DashboardJoueur::class)->name('dashboard.joueur');
	Route::get('/betmaster', BetMaster::class)->name('bet.master');
    
	Route::post('/logout', function(){
		Auth::logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();
		return redirect()->route('login');
	})->name('logout');
});
