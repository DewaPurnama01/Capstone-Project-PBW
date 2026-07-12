<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\InventoriController;
use App\Http\Controllers\KemitraanController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// ================================================================
// AUTH (publik)
// ================================================================
Route::get('/login',    [AuthController::class, 'index'])->name('login');
Route::post('/login',   [AuthController::class, 'login'])->name('auth.login');
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register',[AuthController::class, 'register'])->name('auth.register');
Route::post('/logout',  [AuthController::class, 'logout'])->name('auth.logout');

// Redirect root ke dashboard
Route::get('/', fn() => redirect()->route('dashboard'));

// ================================================================
// PUBLIC: Form Penawaran Petani (via link WhatsApp — tanpa login)
// ================================================================
Route::get('/form-penawaran/{token}',  [KemitraanController::class, 'publicFormPenawaran'])
    ->name('kemitraan.penawaran.public-form');
Route::post('/form-penawaran/{token}', [KemitraanController::class, 'publicStorePenawaran'])
    ->name('kemitraan.penawaran.public-store');

// ================================================================
// PROTECTED ROUTES
// ================================================================
Route::middleware('auth.session')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pelanggan (Owner, Kasir)
    Route::middleware('role:Owner,Kasir')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::get('/',          [PelangganController::class, 'index'])->name('index');
        Route::get('/create',    [PelangganController::class, 'create'])->name('create');
        Route::post('/',         [PelangganController::class, 'store'])->name('store');
        Route::get('/{id}',      [PelangganController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [PelangganController::class, 'edit'])->name('edit');
        Route::put('/{id}',      [PelangganController::class, 'update'])->name('update');
        Route::delete('/{id}',   [PelangganController::class, 'destroy'])->name('destroy');
    });

    // Transaksi (Owner, Kasir)
    Route::middleware('role:Owner,Kasir')->prefix('transaksi')->name('transaksi.')->group(function () {
        Route::get('/',              [TransaksiController::class, 'index'])->name('index');
        Route::get('/create',        [TransaksiController::class, 'create'])->name('create');
        Route::post('/',             [TransaksiController::class, 'store'])->name('store');
        Route::get('/{id}',          [TransaksiController::class, 'show'])->name('show');
        Route::get('/{id}/edit',     [TransaksiController::class, 'edit'])->name('edit');
        Route::put('/{id}',          [TransaksiController::class, 'update'])->name('update');
        Route::delete('/{id}',       [TransaksiController::class, 'destroy'])->name('destroy');
        Route::put('/{id}/status',   [TransaksiController::class, 'updateStatus'])->name('update-status');
    });

    // Inventori (Owner, Admin)
    Route::middleware('role:Owner,Admin')->prefix('inventori')->name('inventori.')->group(function () {
        Route::get('/',        [InventoriController::class, 'index'])->name('index');
        Route::post('/',       [InventoriController::class, 'store'])->name('store');
        Route::put('/{id}',    [InventoriController::class, 'update'])->name('update');
        Route::delete('/{id}', [InventoriController::class, 'destroy'])->name('destroy');
    });

    // Portal Kemitraan (Owner, Admin)
    Route::middleware('role:Owner,Admin')->prefix('kemitraan')->name('kemitraan.')->group(function () {
        Route::get('/', [KemitraanController::class, 'index'])->name('index');

        // Mitra (Petani)
        Route::post('/mitra',       [KemitraanController::class, 'storeMitra'])->name('mitra.store');
        Route::put('/mitra/{id}',   [KemitraanController::class, 'updateMitra'])->name('mitra.update');
        Route::delete('/mitra/{id}',[KemitraanController::class, 'destroyMitra'])->name('mitra.destroy');

        // Broadcast
        Route::post('/broadcast',   [KemitraanController::class, 'storeBroadcast'])->name('broadcast.store');
        Route::put('/broadcast/{id}/tutup', [KemitraanController::class, 'tutupBroadcast'])->name('broadcast.tutup');

        // Penawaran (manual dari Owner untuk mitra yg respond via telepon)
        Route::get('/penawaran/{broadcast_id}',        [KemitraanController::class, 'indexPenawaran'])->name('penawaran.index');
        Route::post('/penawaran/manual',               [KemitraanController::class, 'storePenawaranManual'])->name('penawaran.store-manual');
        Route::put('/penawaran/{id}/pilih',            [KemitraanController::class, 'pilihPenawaran'])->name('penawaran.pilih');

        // QC
        Route::get('/qc/{po_id}',   [KemitraanController::class, 'formQC'])->name('qc.form');
        Route::post('/qc',          [KemitraanController::class, 'storeQC'])->name('qc.store');

        // Hutang
        Route::put('/hutang/{id}/bayar', [KemitraanController::class, 'konfirmasiBayar'])->name('hutang.bayar');

        // Update status PO (DITERBITKAN → DIKIRIM)
        Route::put('/po/{id}/status', [KemitraanController::class, 'updateStatusPO'])->name('po.update-status');
    });

    // Purchase Orders (Owner, Admin)
    Route::middleware('role:Owner,Admin')->prefix('purchase-orders')->name('po.')->group(function () {
        Route::get('/',            [PurchaseOrderController::class, 'index'])->name('index');
        Route::get('/{id}',        [PurchaseOrderController::class, 'show'])->name('show');
        Route::put('/{id}/status', [PurchaseOrderController::class, 'updateStatus'])->name('update-status');
    });

    // Laporan (Owner only)
    Route::middleware('role:Owner')->prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/',            [LaporanController::class, 'index'])->name('index');
        Route::get('/export-pdf',  [LaporanController::class, 'exportPdf'])->name('export-pdf');
    });
});
