<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\AuditLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\FuelDeliveryController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FuelPriceController;
use App\Http\Controllers\Api\FuelController;
use App\Http\Controllers\Web\PumpController;
use App\Http\Controllers\Api\InventoryAdjustmentController;
use App\Http\Controllers\Web\WorkShiftController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\AccountReceivableController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\AccountPayableController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'dashboard'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'home']);

    Route::get('/sales/new', [DashboardController::class, 'newSale'])->middleware('role:admin,supervisor,despachador');

    Route::get('/inventory', [DashboardController::class, 'inventory'])->middleware('role:admin,supervisor');

    Route::get('/reports/daily-close', [DashboardController::class, 'dailyClose'])->middleware('role:admin,supervisor');
    Route::get('/reports/sales-summary', [DashboardController::class, 'salesSummary'])->middleware('role:admin,supervisor');
    Route::get('/reports/cashier-close', [DashboardController::class, 'cashierClose'])->middleware('role:admin,supervisor');
    Route::get('/reports/cashier-close-range', [DashboardController::class, 'cashierCloseRange'])->middleware('role:admin,supervisor');

    Route::get('/reports/sales-summary/pdf', [DashboardController::class, 'salesSummaryPdf'])->middleware('role:admin,supervisor');
    Route::get('/reports/cashier-close-range/pdf', [DashboardController::class, 'cashierCloseRangePdf'])->middleware('role:admin,supervisor');

    Route::get('/expenses', [DashboardController::class, 'expenses'])->middleware('role:admin,supervisor');
    Route::get('/expenses/new', [DashboardController::class, 'newExpense'])->middleware('role:admin,supervisor');
    Route::get('/expenses/pdf', [DashboardController::class, 'expensesPdf'])->middleware('role:admin,supervisor');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', [UserController::class, 'index'])->middleware('role:admin');
    Route::get('/users/new', [UserController::class, 'create'])->middleware('role:admin');
    Route::post('/users', [UserController::class, 'store'])->middleware('role:admin');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->middleware('role:admin');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('role:admin');
    Route::get('/fuel-prices', [DashboardController::class, 'fuelPrices'])->middleware('role:admin,supervisor');
    Route::get('/fuel-prices/new', [DashboardController::class, 'newFuelPrice'])->middleware('role:admin,supervisor');
    Route::get('/fuel-deliveries/new', [DashboardController::class, 'newFuelDelivery'])->middleware('role:admin,supervisor');
    Route::post('/sales', [SaleController::class, 'store'])->middleware('role:admin,supervisor,despachador');
Route::post('/fuel-deliveries', [FuelDeliveryController::class, 'store'])->middleware('role:admin,supervisor');
Route::post('/expenses', [ExpenseController::class, 'store'])->middleware('role:admin,supervisor');
Route::post('/fuel-prices', [FuelPriceController::class, 'store'])->middleware('role:admin,supervisor');

Route::get('/fuels/{fuel}/current-price', [FuelController::class, 'currentPrice'])->middleware('role:admin,supervisor,despachador');
Route::get('/fuels-with-prices', [FuelController::class, 'fuelsWithPrices'])->middleware('role:admin,supervisor');
Route::get('/expenses-data', [ExpenseController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/pumps', [PumpController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/pumps/new', [PumpController::class, 'create'])->middleware('role:admin,supervisor');
Route::post('/pumps', [PumpController::class, 'store'])->middleware('role:admin,supervisor');

Route::get('/pumps/{pump}/edit', [PumpController::class, 'edit'])->middleware('role:admin,supervisor');
Route::put('/pumps/{pump}', [PumpController::class, 'update'])->middleware('role:admin,supervisor');

Route::post('/pumps/{pump}/nozzles', [PumpController::class, 'storeNozzle'])->middleware('role:admin,supervisor');
Route::put('/nozzles/{nozzle}', [PumpController::class, 'updateNozzle'])->middleware('role:admin,supervisor');
Route::get('/nozzles/{nozzle}/edit', [PumpController::class, 'editNozzle'])->middleware('role:admin,supervisor');
Route::get('/reports', [DashboardController::class, 'reports'])->middleware('role:admin,supervisor');
});
Route::post('/sales/{sale}/void', [SaleController::class, 'void'])->middleware('role:admin,supervisor');
Route::get('/sales', [DashboardController::class, 'salesIndex'])->middleware('role:admin,supervisor');
Route::post('/fuel-deliveries/{fuelDelivery}/void', [FuelDeliveryController::class, 'void'])->middleware('role:admin,supervisor');
Route::get('/fuel-deliveries', [DashboardController::class, 'fuelDeliveriesIndex'])->middleware('role:admin,supervisor');
Route::get('/fuel-deliveries/pdf', [DashboardController::class, 'fuelDeliveriesPdf'])->middleware('role:admin,supervisor');
Route::post('/inventory-adjustments', [InventoryAdjustmentController::class, 'store'])->middleware('role:admin,supervisor');
Route::get('/inventory-adjustments/new', [DashboardController::class, 'newInventoryAdjustment'])->middleware('role:admin,supervisor');
Route::post('/expenses/{expense}/void',[ExpenseController::class,'void'])
    ->middleware('role:admin,supervisor');
Route::get('/cashier-dashboard', [DashboardController::class, 'cashierDashboard'])
    ->middleware('role:despachador');
Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('role:admin');
Route::get('/audit-logs/pdf', [AuditLogController::class, 'pdf'])->middleware('role:admin');
Route::get('/audit-logs/dashboard', [AuditLogController::class, 'dashboard'])->middleware('role:admin');
Route::get('/work-shifts', [WorkShiftController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/work-shifts/open', [WorkShiftController::class, 'create'])->middleware('role:admin,supervisor');
Route::post('/work-shifts', [WorkShiftController::class, 'store'])->middleware('role:admin,supervisor');
Route::get('/work-shifts/{workShift}', [WorkShiftController::class, 'show'])->middleware('role:admin,supervisor');
Route::post('/work-shifts/{workShift}/close', [WorkShiftController::class, 'close'])->middleware('role:admin,supervisor');
Route::get('/work-shifts/{workShift}/pdf', [WorkShiftController::class, 'pdf'])->middleware('role:admin,supervisor');
Route::get('/work-shifts-report', [WorkShiftController::class, 'report'])->middleware('role:admin,supervisor');
Route::get('/work-shifts-report/pdf', [WorkShiftController::class, 'reportPdf'])->middleware('role:admin,supervisor');
Route::get('/customers', [CustomerController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/customers/new', [CustomerController::class, 'create'])->middleware('role:admin,supervisor');
Route::post('/customers', [CustomerController::class, 'store'])->middleware('role:admin,supervisor');
Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('role:admin,supervisor');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('role:admin,supervisor');
Route::get('/accounts-receivable', [AccountReceivableController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/accounts-receivable/pdf', [AccountReceivableController::class, 'pdf'])->middleware('role:admin,supervisor');
Route::get('/accounts-receivable/{accountReceivable}/pdf', [AccountReceivableController::class, 'accountPdf'])->middleware('role:admin,supervisor');
Route::get('/accounts-receivable/{accountReceivable}', [AccountReceivableController::class, 'show'])->middleware('role:admin,supervisor');
Route::post('/accounts-receivable/{accountReceivable}/payments', [AccountReceivableController::class, 'storePayment'])->middleware('role:admin,supervisor');
Route::get('/customers/{customer}/statement', [AccountReceivableController::class, 'customerStatement'])->middleware('role:admin,supervisor');
Route::get('/customers/{customer}/statement/pdf', [AccountReceivableController::class, 'customerStatementPdf'])->middleware('role:admin,supervisor');
Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/suppliers/new', [SupplierController::class, 'create'])->middleware('role:admin,supervisor');
Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('role:admin,supervisor');
Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->middleware('role:admin,supervisor');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('role:admin,supervisor');
Route::get('/accounts-payable', [AccountPayableController::class, 'index'])->middleware('role:admin,supervisor');
Route::get('/accounts-payable/new', [AccountPayableController::class, 'create'])->middleware('role:admin,supervisor');
Route::post('/accounts-payable', [AccountPayableController::class, 'store'])->middleware('role:admin,supervisor');
Route::post('/accounts-payable/{accountPayable}/payments', [AccountPayableController::class, 'storePayment'])->middleware('role:admin,supervisor');
Route::get('/suppliers/{supplier}/statement', [AccountPayableController::class, 'supplierStatement'])->middleware('role:admin,supervisor');
Route::get('/accounts-payable/pdf', [AccountPayableController::class, 'pdf'])->middleware('role:admin,supervisor');
Route::get('/suppliers/{supplier}/statement/pdf', [AccountPayableController::class, 'supplierStatementPdf'])->middleware('role:admin,supervisor');
Route::get('/accounts-payable/{accountPayable}/pdf', [AccountPayableController::class, 'accountPdf'])->middleware('role:admin,supervisor');
Route::get('/accounts-payable/{accountPayable}', [AccountPayableController::class, 'show'])->middleware('role:admin,supervisor');
Route::get('/reports/financial-summary', [DashboardController::class, 'financialSummary'])->middleware('role:admin,supervisor');
Route::get('/reports/financial-summary/pdf', [DashboardController::class, 'financialSummaryPdf'])->middleware('role:admin,supervisor');



require __DIR__.'/auth.php';
