<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    ClientApiController,
    OrderApiController,
    CustomOrderApiController,
    ProductApiController,
    DashboardApiController,
    StockApiController,
    MaintenanceApiController,
    DeliveryApiController,
    QuoteApiController,
    SalaryApiController,
    UserApiController,
    PurchaseOrderApiController,
};

// ─── Auth publique ─────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('/login',  [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    // ─── Routes protégées ─────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/user', [AuthController::class, 'me']);
        Route::put('/user/profile', [AuthController::class, 'updateProfile']);
        Route::put('/user/password', [AuthController::class, 'changePassword']);

        // Dashboard
        Route::get('/dashboard', [DashboardApiController::class, 'index']);
        Route::get('/dashboard/revenue-chart', [DashboardApiController::class, 'revenueChart']);
        Route::get('/dashboard/kpis', [DashboardApiController::class, 'kpis']);

        // Clients
        Route::apiResource('clients', ClientApiController::class);
        Route::get('clients/{client}/measurements', [ClientApiController::class, 'measurements']);
        Route::post('clients/{client}/measurements', [ClientApiController::class, 'storeMeasurement']);

        // Produits (catalogue)
        Route::get('products', [ProductApiController::class, 'index']);
        Route::get('products/{product}', [ProductApiController::class, 'show']);
        Route::post('products', [ProductApiController::class, 'store'])->middleware('role:admin,stock_manager');
        Route::put('products/{product}', [ProductApiController::class, 'update'])->middleware('role:admin,stock_manager');
        Route::delete('products/{product}', [ProductApiController::class, 'destroy'])->middleware('role:admin');

        // Ventes
        Route::apiResource('orders', OrderApiController::class);
        Route::post('orders/{order}/payment', [OrderApiController::class, 'payment']);
        Route::put('orders/{order}/status', [OrderApiController::class, 'updateStatus']);

        // Sur mesure
        Route::apiResource('custom-orders', CustomOrderApiController::class);
        Route::put('custom-orders/{customOrder}/status', [CustomOrderApiController::class, 'updateStatus']);
        Route::put('custom-orders/{customOrder}/assign', [CustomOrderApiController::class, 'assign']);
        Route::post('custom-orders/{customOrder}/payment', [CustomOrderApiController::class, 'payment']);

        // Stock
        Route::get('stock', [StockApiController::class, 'index']);
        Route::get('stock/low', [StockApiController::class, 'lowStock']);
        Route::post('stock/add', [StockApiController::class, 'addStock'])->middleware('role:admin,stock_manager');
        Route::post('stock/adjust', [StockApiController::class, 'adjust'])->middleware('role:admin');
        Route::get('stock/movements', [StockApiController::class, 'movements']);
        Route::post('stock/movements', [StockApiController::class, 'storeMovement'])->middleware('role:admin,stock_manager');

        // Maintenance
        Route::get('equipment', [MaintenanceApiController::class, 'equipment']);
        Route::post('equipment', [MaintenanceApiController::class, 'storeEquipment'])->middleware('role:admin');
        Route::put('equipment/{id}', [MaintenanceApiController::class, 'updateEquipment'])->middleware('role:admin');
        Route::delete('equipment/{id}', [MaintenanceApiController::class, 'destroyEquipment'])->middleware('role:admin');
        Route::get('maintenance', [MaintenanceApiController::class, 'index']);
        Route::post('maintenance', [MaintenanceApiController::class, 'store']);
        Route::put('maintenance/{log}/resolve', [MaintenanceApiController::class, 'resolve']);

        // Livraisons
        Route::get('deliveries', [DeliveryApiController::class, 'index']);
        Route::post('deliveries', [DeliveryApiController::class, 'store']);
        Route::put('deliveries/{delivery}/status', [DeliveryApiController::class, 'updateStatus']);
        Route::post('deliveries/{delivery}/proof', [DeliveryApiController::class, 'uploadProof']);
        Route::put('deliveries/{delivery}/location', [DeliveryApiController::class, 'updateLocation']);

        // Devis
        Route::get('quotes', [QuoteApiController::class, 'index']);
        Route::post('quotes', [QuoteApiController::class, 'store']);
        Route::get('quotes/{quote}', [QuoteApiController::class, 'show']);
        Route::put('quotes/{quote}', [QuoteApiController::class, 'update']);
        Route::put('quotes/{quote}/status', [QuoteApiController::class, 'updateStatus']);
        Route::delete('quotes/{quote}', [QuoteApiController::class, 'destroy'])->middleware('role:admin');

        // Salaires (admin seulement)
        Route::middleware('role:admin')->group(function () {
            Route::get('salaries', [SalaryApiController::class, 'index']);
            Route::post('salaries', [SalaryApiController::class, 'store']);
            Route::get('salaries/{salary}', [SalaryApiController::class, 'show']);
            Route::get('salaries/employees/list', [SalaryApiController::class, 'employees']);
        });

        // Utilisateurs (admin seulement)
        Route::middleware('role:admin')->group(function () {
            Route::get('users', [UserApiController::class, 'index']);
            Route::post('users', [UserApiController::class, 'store']);
            Route::put('users/{id}', [UserApiController::class, 'update']);
            Route::put('users/{id}/toggle-active', [UserApiController::class, 'toggleActive']);
            Route::put('users/{id}/reset-password', [UserApiController::class, 'resetPassword']);
        });

        // Bons de commande / Achats
        Route::middleware('role:admin,stock_manager')->group(function () {
            Route::get('purchase-orders', [PurchaseOrderApiController::class, 'index']);
            Route::post('purchase-orders', [PurchaseOrderApiController::class, 'store']);
            Route::put('purchase-orders/{id}', [PurchaseOrderApiController::class, 'update']);
            Route::delete('purchase-orders/{id}', [PurchaseOrderApiController::class, 'destroy'])->middleware('role:admin');
        });

        // Finance (admin seulement)
        Route::middleware('role:admin')->group(function () {
            Route::get('finance/report', [\App\Http\Controllers\Api\FinanceApiController::class, 'report']);
            Route::get('finance/expenses', [\App\Http\Controllers\Api\FinanceApiController::class, 'expenses']);
            Route::post('finance/expenses', [\App\Http\Controllers\Api\FinanceApiController::class, 'storeExpense']);
        });

        // Notifications
        Route::get('notifications', function () {
            return auth()->user()->notifications()->paginate(20);
        });
        Route::put('notifications/{id}/read', function ($id) {
            auth()->user()->notifications()->find($id)?->markAsRead();
            return response()->json(['success' => true]);
        });
        Route::put('notifications/read-all', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        });
    });
});
