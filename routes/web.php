<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MailTestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Weather update
    Route::post('/weather/update', [DashboardController::class, 'updateWeather'])->name('weather.update');
    
    // Activities
    Route::resource('activities', ActivityController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Vouchers
    Route::get('/premium', [VoucherController::class, 'showActivationForm'])->name('premium');
    Route::post('/premium/generate', [VoucherController::class, 'generateCode'])->name('premium.generate');
    Route::post('/voucher/activate', [VoucherController::class, 'activate'])->name('voucher.activate');
    
    // Mail Testing (Development)
    Route::get('/mail/preview', [MailTestController::class, 'preview'])->name('mail.preview');
    Route::post('/mail/test', [MailTestController::class, 'send'])->name('mail.test.send');
    Route::get('/mail/send-test', [MailTestController::class, 'sendTest'])->name('mail.sendtest');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
