<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Support\Login as SupportLogin;
use App\Livewire\Support\Ticket\Show as SupportTickets;
use App\Livewire\Support\Ticket\Details as SupportTicketDetails;

// صفحة تسجيل الدخول متاحة للجميع
Route::get('support/login', SupportLogin::class)->name('support.login');

// بقية صفحات الدعم تتطلب تسجيل الدخول
Route::middleware(['web', 'support'])
    ->prefix('support')
    ->group(function () {
        Route::get('tickets', SupportTickets::class)->name('support.tickets');
        Route::get('tickets/{ticket}', SupportTicketDetails::class)->name('support.tickets.details');
    });

Route::get('support/logout', function() {
    \Illuminate\Support\Facades\Cache::forget('admin');
    return redirect()->route('support.login');
})->name('support.logout');
