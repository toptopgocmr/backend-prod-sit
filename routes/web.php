<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    DashboardController,
    CustomOrderController,
    OrderController,
    ClientController,
    ProductController,
    CategoryController,
    StockController,
    FinanceController,
    ExpenseController,
    DeliveryController,
    EquipmentController,
    MaintenanceController,
    UserController,
    ReportController,
    SettingController,
};
use App\Http\Controllers\Auth\{LoginController, ProfileController};

// ─── Healthcheck (public, no auth) ────────────────────────────────────────
Route::get('/health', fn() => response()->json(['status' => 'ok'], 200));

// ─── Auth ─────────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ─── Application protégée ─────────────────────────────────────────────────
Route::middleware(['auth', 'check.active'])->group(function () {

    // Dashboard (tout le monde)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Profil (tout le monde)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // ─── Clients — admin + cashier ─────────────────────────────────
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('clients/alerts', [ClientController::class, 'alerts'])->name('clients.alerts');
        Route::resource('clients', ClientController::class);
        Route::get('clients/{client}/measurements', [ClientController::class, 'measurements'])->name('clients.measurements');
        Route::post('clients/{client}/measurements', [ClientController::class, 'storeMeasurement'])->name('clients.measurements.store');
        Route::put('clients/{client}/measurements/{measurement}', [ClientController::class, 'updateMeasurement'])->name('clients.measurements.update');
        Route::delete('clients/{client}/measurements/{measurement}', [ClientController::class, 'destroyMeasurement'])->name('clients.measurements.destroy');
    });

    // ─── Ventes — admin + cashier ──────────────────────────────────
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
        Route::resource('orders', OrderController::class);
        Route::post('orders/{order}/payment', [OrderController::class, 'recordPayment'])->name('orders.payment');
        Route::put('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
        Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    });

    // ─── Commandes sur mesure — admin + cashier + couturier ────────
    Route::middleware('role:admin,cashier,couturier')->group(function () {
        Route::get('custom-orders/export', [CustomOrderController::class, 'export'])->name('custom-orders.export');
        Route::get('custom-orders/corbeille', [CustomOrderController::class, 'corbeille'])->name('custom-orders.corbeille');
        Route::put('custom-orders/{id}/restaurer', [CustomOrderController::class, 'restaurer'])->name('custom-orders.restaurer');
        Route::delete('custom-orders/{id}/purger', [CustomOrderController::class, 'purger'])->name('custom-orders.purger');
        Route::resource('custom-orders', CustomOrderController::class);
        Route::put('custom-orders/{customOrder}/status', [CustomOrderController::class, 'updateStatus'])->name('custom-orders.status');
        Route::put('custom-orders/{customOrder}/assign', [CustomOrderController::class, 'assignCouturier'])->name('custom-orders.assign');
        Route::post('custom-orders/{customOrder}/payment', [CustomOrderController::class, 'recordPayment'])->name('custom-orders.payment');
        Route::get('custom-orders/{customOrder}/fiche', [CustomOrderController::class, 'printFiche'])->name('custom-orders.fiche');
        Route::post('custom-orders/{customOrder}/measures', [CustomOrderController::class, 'saveMeasures'])->name('custom-orders.saveMeasures');
        // ── Devis ──
        Route::resource('quotes', \App\Http\Controllers\Admin\QuoteController::class);
        Route::put('quotes/{quote}/status', [\App\Http\Controllers\Admin\QuoteController::class, 'updateStatus'])->name('quotes.status');
        Route::post('quotes/{quote}/convert', [\App\Http\Controllers\Admin\QuoteController::class, 'convertToOrder'])->name('quotes.convert');
        Route::get('quotes/{quote}/pdf', [\App\Http\Controllers\Admin\QuoteController::class, 'pdf'])->name('quotes.pdf');
    });

    // ─── Produits — admin + stock_manager ──────────────────────────
    Route::middleware('role:admin,stock_manager')->group(function () {
        Route::resource('products', ProductController::class);
        Route::post('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');
        Route::get('products/export', [ProductController::class, 'export'])->name('products.export');

        // ─── Catégories ────────────────────────────────────────────
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::put('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
    });

    // ─── Stock — admin + stock_manager ─────────────────────────────
    Route::middleware('role:admin,stock_manager')->prefix('stock')->name('stock.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/movements', [StockController::class, 'movements'])->name('movements');
        Route::post('/add', [StockController::class, 'addStock'])->name('add');
        Route::post('/adjust', [StockController::class, 'adjust'])->name('adjust');
        Route::get('/low-stock', [StockController::class, 'lowStock'])->name('low');
        Route::get('/inventory', [StockController::class, 'inventory'])->name('inventory');
        // Exports
        Route::get('/export/excel',           [StockController::class, 'exportExcel'])          ->name('export.excel');
        Route::get('/export/pdf',             [StockController::class, 'exportPdf'])            ->name('export.pdf');
        Route::get('/export/low-stock/pdf',   [StockController::class, 'exportLowStockPdf'])    ->name('export.low-stock-pdf');
        Route::get('/export/movements/excel', [StockController::class, 'exportMovementsExcel']) ->name('export.movements-excel');
    });

    // ─── Bons de commande — admin + stock_manager ──────────────────
    Route::middleware('role:admin,stock_manager')->group(function () {
        Route::resource('purchase-orders', \App\Http\Controllers\Admin\PurchaseOrderController::class);
        Route::put('purchase-orders/{purchaseOrder}/receive', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::put('purchase-orders/{purchaseOrder}/cancel', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');
    });

    // ─── Finance — admin + cashier ─────────────────────────────────
    Route::middleware('role:admin,cashier')->group(function () {
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::get('/report/{year?}/{month?}', [FinanceController::class, 'monthlyReport'])->name('report');
            Route::get('/cashflow', [FinanceController::class, 'cashflow'])->name('cashflow');
        });

        // Dépenses
        Route::resource('expenses', ExpenseController::class);
        Route::put('expenses/{expense}/validate', [ExpenseController::class, 'validateExpense'])->name('expenses.validate');

        // Salaires
        Route::get('salaries', [\App\Http\Controllers\Admin\SalaryController::class, 'index'])->name('salaries.index');
        Route::post('salaries', [\App\Http\Controllers\Admin\SalaryController::class, 'store'])->name('salaries.store');
        Route::get('salaries/export/{month}/{year}', [\App\Http\Controllers\Admin\SalaryController::class, 'export'])->name('salaries.export');
    });

    // ─── Livraisons — admin + delivery ─────────────────────────────
    Route::middleware('role:admin,delivery')->group(function () {
        Route::resource('deliveries', DeliveryController::class);
        Route::put('deliveries/{delivery}/assign', [DeliveryController::class, 'assignDriver'])->name('deliveries.assign');
        Route::put('deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('deliveries.status');
        Route::post('deliveries/{delivery}/proof', [DeliveryController::class, 'uploadProof'])->name('deliveries.proof');
    });

    // ─── Atelier — admin + couturier ───────────────────────────────
    Route::middleware('role:admin,couturier')->prefix('atelier')->name('atelier.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AtelierController::class, 'index'])->name('index');
        Route::get('/planning', [\App\Http\Controllers\Admin\AtelierController::class, 'planning'])->name('planning');
        Route::get('/performance', [\App\Http\Controllers\Admin\AtelierController::class, 'performance'])->name('performance');
    });

    // ─── Maintenance — admin seulement ─────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::resource('equipment', EquipmentController::class);
        Route::resource('maintenance', MaintenanceController::class);
        Route::put('maintenance/{maintenance}/resolve', [MaintenanceController::class, 'resolve'])->name('maintenance.resolve');
        Route::put('maintenance/{maintenance}/assign', [MaintenanceController::class, 'assign'])->name('maintenance.assign');
    });

    // ─── Rapports — admin + cashier ────────────────────────────────
    Route::middleware('role:admin,cashier')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/clients', [ReportController::class, 'clients'])->name('clients');
        Route::get('/finance', [ReportController::class, 'finance'])->name('finance');
        Route::get('/export', [ReportController::class, 'export'])->name('export');
    });

    // ─── Utilisateurs (admin seulement) ─�