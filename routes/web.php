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
use App\Livewire\Auth\CheckIn;
use Illuminate\Support\Facades\Auth;

Route::get('/check-in', CheckIn::class)->name('login');

Route::middleware(['auth'])->group(function(){
	Route::get('/player/dashboard', DashboardJoueur::class)->name('dashboard.joueur');
    
	Route::post('/logout', function(){
		Auth::logout();
		request()->session()->invalidate();
		request()->session()->regenerateToken();
		return redirect()->route('login');
	})->name('logout');
});
