<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\SpbController;
use App\Http\Controllers\Auth\Login;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/generate-pdf/{id}', [PdfController::class, 'generatePdf'])->name('generate-pdf');
// Ensure this route is correctly defined


Route::get('/cetak-pdf/{id}', [TicketController::class, 'cetakPdf'])->name('cetak-pdf');
Route::get('/spb-pdf/{id}', [SpbController::class, 'spbPdf'])->name('spb-pdf');

Route::get('/storage/bukti_bayar/{filename}', function ($filename) {
    $path = storage_path('app/public/bukti_bayar/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
});