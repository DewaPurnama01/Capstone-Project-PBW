<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PartnershipController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

/**
 * ROUTING = pemetaan "alamat URL + method HTTP" ke fungsi controller
 * yang akan menangani request tsb. File ini adalah "peta" seluruh API.
 *
 * Route::middleware('jwt.auth') membungkus semua route di dalamnya supaya
 * WAJIB mengirim token JWT yang valid (lihat app/Http/Middleware/JwtMiddleware.php).
 *
 * Route::middleware('role:owner,admin') di dalamnya lagi membatasi lebih
 * jauh: dari semua user yang sudah login, hanya role tertentu yang boleh
 * mengakses modul itu — inilah penerapan RBAC (Role-Based Access Control)
 * sesuai tabel akses di laporan bagian 2.
 */

// ================================================================
// AUTH (tidak perlu login untuk mengakses ini)
// ================================================================
Route::post('/auth/login', [AuthController::class, 'login']);

// ================================================================
// SEMUA ROUTE DI BAWAH INI WAJIB LOGIN (token JWT valid)
// ================================================================
Route::middleware('jwt.auth')->group(function () {

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);

    // Dashboard bisa diakses oleh semua role yang sudah login
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Manajemen Pelanggan -> hanya Owner & Kasir (lihat tabel akses laporan bagian 2)
    Route::middleware('role:owner,kasir')->prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index']);
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/{customer}', [CustomerController::class, 'show']);
        Route::put('/{customer}', [CustomerController::class, 'update']);
        Route::delete('/{customer}', [CustomerController::class, 'destroy']);
        Route::post('/{customer}/points', [CustomerController::class, 'addPoints']);
    });

    // Transaksi & POS -> Owner & Kasir
    Route::middleware('role:owner,kasir')->prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/products', [TransactionController::class, 'products']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{transaction}', [TransactionController::class, 'show']);
        Route::put('/{transaction}/status', [TransactionController::class, 'updateStatus']);
        Route::delete('/{transaction}', [TransactionController::class, 'destroy']);
    });

    // Manajemen Inventori -> Owner & Admin
    Route::middleware('role:owner,admin')->prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::post('/', [InventoryController::class, 'store']);
        Route::put('/{inventoryItem}', [InventoryController::class, 'update']);
        Route::post('/{inventoryItem}/restock', [InventoryController::class, 'restock']);
        Route::delete('/{inventoryItem}', [InventoryController::class, 'destroy']);
    });

    // Portal Kemitraan -> Owner & Admin
    Route::middleware('role:owner,admin')->prefix('partnership')->group(function () {
        Route::get('/', [PartnershipController::class, 'index']);
        Route::get('/partners', [PartnershipController::class, 'partners']);
        Route::post('/requests', [PartnershipController::class, 'createRequest']);
        Route::post('/requests/{restockRequest}/broadcast', [PartnershipController::class, 'broadcast']);
        Route::post('/requests/{restockRequest}/offers', [PartnershipController::class, 'submitOffer']);
        Route::post('/offers/{offer}/select', [PartnershipController::class, 'selectOffer']);
    });

    // Purchase Orders -> Owner & Admin
    Route::middleware('role:owner,admin')->prefix('purchase-orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show']);
        Route::post('/{purchaseOrder}/payments', [PurchaseOrderController::class, 'recordPayment']);
        Route::post('/{purchaseOrder}/receive', [PurchaseOrderController::class, 'confirmReceipt']);
    });

    // Laporan & Analitik -> Owner saja (fitur eksklusif, sesuai laporan bagian 4.8)
    Route::middleware('role:owner')->prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
    });
});
