<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EduMatchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EduMatchController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/iniciar-sesion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/iniciar-sesion', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::post('/salir', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/inicio', [EduMatchController::class, 'dashboard'])->name('dashboard');
    Route::get('/perfil', [EduMatchController::class, 'editProfile'])->name('profile.edit');
    Route::put('/perfil', [EduMatchController::class, 'updateProfile'])->name('profile.update');

    Route::get('/mentores', [EduMatchController::class, 'mentors'])->name('mentors.index');
    Route::get('/mentores/{mentor}', [EduMatchController::class, 'showMentor'])->name('mentors.show');
    Route::get('/mentores/{mentor}/sesiones/crear', [EduMatchController::class, 'createSession'])->name('sessions.create');

    Route::get('/disponibilidad', [EduMatchController::class, 'availability'])->name('availability.index');
    Route::post('/disponibilidad', [EduMatchController::class, 'storeAvailability'])->name('availability.store');
    Route::delete('/disponibilidad/{availability}', [EduMatchController::class, 'deleteAvailability'])->name('availability.destroy');

    Route::get('/sesiones', [EduMatchController::class, 'sessions'])->name('sessions.index');
    Route::post('/sesiones', [EduMatchController::class, 'storeSession'])->name('sessions.store');
    Route::get('/sesiones/{session}', [EduMatchController::class, 'showSession'])->name('sessions.show');
    Route::post('/sesiones/{session}/mensajes', [EduMatchController::class, 'sendMessage'])->name('sessions.message');
    Route::post('/sesiones/{session}/estado/{status}', [EduMatchController::class, 'updateSessionStatus'])->name('sessions.status');
    Route::post('/sesiones/{session}/calificacion', [EduMatchController::class, 'storeReview'])->name('sessions.review');
});
