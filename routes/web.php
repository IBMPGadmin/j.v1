<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\JurisUserTextController;
use App\Http\Controllers\GovernmentLinkController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

// Subscription routes
Route::get('/pricing', [\App\Http\Controllers\SubscriptionController::class, 'showPricing'])->name('subscription.pricing');
Route::get('/subscription/test-cards', [\App\Http\Controllers\SubscriptionController::class, 'testCards'])->name('subscription.test-cards');
Route::get('/subscription/test-checkout', [\App\Http\Controllers\StripeTestController::class, 'testStripeCheckout'])->name('subscription.test-checkout');
Route::get('/subscription/debug-stripe', [\App\Http\Controllers\StripeDebugController::class, 'checkConfig'])->name('subscription.debug-stripe');

Route::middleware(['auth'])->group(function () {
    Route::post('/subscription/{package}/purchase', [\App\Http\Controllers\SubscriptionController::class, 'purchase'])->name('subscription.purchase');
    Route::get('/subscription/success', [\App\Http\Controllers\SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
});

// Admin-only routes
Route::middleware([\App\Http\Middleware\Authenticate::class, 'verified', \App\Http\Middleware\AdminOnly::class])->group(function () {
    Route::get('/admin-dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    // Admin: Add new user page and store
    Route::get('/admin/users/add', [UserController::class, 'create'])->name('admin.users.add');
    Route::post('/admin/users/add', [UserController::class, 'store'])->name('admin.users.store');

    // All users page
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [UserController::class, 'show'])->name('admin.users.show');
    Route::patch('/admin/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('admin.users.toggle');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.delete');

    // Payment Dashboard
    Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentDetailsController::class, 'index'])->name('admin.payments.index');
    Route::get('/admin/payments/export', [\App\Http\Controllers\Admin\PaymentDetailsController::class, 'export'])->name('admin.payments.export');
    Route::get('/admin/payments/{subscription}', [\App\Http\Controllers\Admin\PaymentDetailsController::class, 'show'])->name('admin.payments.view');
    
    // Users Report
    Route::get('/admin/reports/users', [\App\Http\Controllers\Admin\UserReportController::class, 'index'])->name('admin.reports.users');
    Route::get('/admin/reports/users/export', [\App\Http\Controllers\Admin\UserReportController::class, 'export'])->name('admin.reports.users.export');

    // Government Links routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('government-links', \App\Http\Controllers\GovernmentLinkController::class);
        Route::resource('rcic-deadlines', \App\Http\Controllers\RCICDeadlineController::class);
        Route::resource('legal-key-terms', \App\Http\Controllers\LegalKeyTermController::class);
    });

    // Legal Documents routes
    Route::get('/admin/legal-documents/add/standard', [\App\Http\Controllers\Admin\LegalDocumentController::class, 'standardUpload'])->name('admin.legal-documents.standard');
    Route::get('/admin/legal-documents/add/alternative', [\App\Http\Controllers\Admin\LegalDocumentController::class, 'alternativeUpload'])->name('admin.legal-documents.alternative');
    Route::post('/admin/legal-documents/process-standard', [\App\Http\Controllers\Admin\LegalDocumentController::class, 'processStandardUpload'])->name('admin.legal-documents.process-standard');
    Route::post('/admin/legal-documents/process-alternative', [\App\Http\Controllers\Admin\LegalDocumentController::class, 'processAlternativeUpload'])->name('admin.legal-documents.process-alternative');
    
    // Legacy route for backward compatibility
    Route::get('/admin/legal-documents/add', function () {
        return redirect()->route('admin.legal-documents.standard');
    })->name('admin.legal-documents.add');

    // Legacy upload handler for backward compatibility
    Route::post('/admin/legal-documents/add', [\App\Http\Controllers\Admin\LegalDocumentController::class, 'processStandardUpload'])->name('admin.legal-documents.store');
});

// User-only routes
Route::middleware([\App\Http\Middleware\Authenticate::class, 'verified', \App\Http\Middleware\UserOnly::class, \App\Http\Middleware\CheckSubscription::class])->group(function () {
    Route::get('/user-dashboard', function () {
        return view('user-dashboard');
    })->name('user.dashboard');

    // RCIC Deadlines for users
    Route::get('/rcic-deadlines', [App\Http\Controllers\User\RCICDeadlineController::class, 'index'])->name('user.rcic-deadlines.index');
    
    // Legal Key Terms for users
    Route::get('/legal-key-terms', [App\Http\Controllers\User\LegalKeyTermController::class, 'index'])->name('user.legal-key-terms.index');

    // Client routes
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::post('/select-client', [ClientController::class, 'selectClient'])->name('clients.select');
    Route::get('/home', [ClientController::class, 'home'])->name('home');
    Route::get('/templates', [ClientController::class, 'viewTemplates'])->name('templates');
    Route::get('/legal-tables/{id}', [ClientController::class, 'viewLegalTable'])->name('client.legalTables.view');
    
    // Government Links routes for users
    Route::get('/government-links', [App\Http\Controllers\UserGovernmentLinksController::class, 'index'])->name('user.government-links');
    Route::get('/government-links/{category}', [App\Http\Controllers\UserGovernmentLinksController::class, 'showCategory'])->name('user.government-links.category');
    
    // Legal table view and annotation routes
    Route::get('/view-legal-table', [App\Http\Controllers\ViewLegalTableController::class, 'show'])->name('view-legal-table');
    Route::get('/section-content/{tableId}/{sectionRef}', [App\Http\Controllers\ViewLegalTableController::class, 'getSectionContent'])->name('section-content');
    Route::get('/reference/{referenceId}', [App\Http\Controllers\ViewLegalTableController::class, 'fetchReferenceById'])->name('reference.fetch');
    Route::post('/annotations', [App\Http\Controllers\ViewLegalTableController::class, 'saveAnnotation'])->name('annotations.save');
    Route::delete('/annotations/{id}', [App\Http\Controllers\ViewLegalTableController::class, 'deleteAnnotation'])->name('annotations.delete');
    
    // Document routes
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/search', [DocumentController::class, 'search'])->name('documents.search');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/documents/{id}/download', [DocumentController::class, 'download'])->name('documents.download');
    
    // User text annotations routes
    Route::post('/annotations', [JurisUserTextController::class, 'store'])->name('annotations.store');
    Route::get('/annotations/section', [JurisUserTextController::class, 'getForSection'])->name('annotations.section');
    Route::patch('/annotations/{id}', [JurisUserTextController::class, 'update'])->name('annotations.update');
    Route::delete('/annotations/{id}', [JurisUserTextController::class, 'destroy'])->name('annotations.destroy');

    // Legal tables route
    Route::get('/user/client/{client}/legal-tables', [App\Http\Controllers\UserLegalTableController::class, 'show'])
        ->name('user.client.legal-tables');
        
    // Payment Details routes
    Route::get('/payment/details', [App\Http\Controllers\PaymentDetailsController::class, 'index'])->name('payment.details');
    Route::post('/payment/subscription/{id}/cancel', [App\Http\Controllers\PaymentDetailsController::class, 'cancelSubscription'])->name('payment.subscription.cancel');
    Route::get('/payment/subscription/activate/{packageId}', [App\Http\Controllers\PaymentDetailsController::class, 'activateNewPackage'])->name('payment.subscription.activate');
});

Route::middleware(\App\Http\Middleware\Authenticate::class)->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Add an alias route for backward compatibility
// Route::get('/legal-tables/{id}', [ClientController::class, 'viewLegalTable'])->name('client.legalTables');
Route::get('/view-legal-table/{table}', [App\Http\Controllers\ViewLegalTableController::class, 'show'])
    ->name('view.legal.table');

// API routes for legal table content - no redirect, always return JSON
Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::get('/section-content/{tableId}/{sectionRef}', [App\Http\Controllers\ViewLegalTableController::class, 'getSectionContent'])
        ->name('api.section-content')
        ->middleware('App\Http\Middleware\JsonAuthenticate')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        
    Route::get('/reference/{referenceId}', [App\Http\Controllers\ViewLegalTableController::class, 'fetchReferenceById'])
        ->name('api.reference.fetch')
        ->middleware('App\Http\Middleware\JsonAuthenticate')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        
    Route::post('/annotations', [App\Http\Controllers\ViewLegalTableController::class, 'saveAnnotation'])
        ->name('api.annotations.save')
        ->middleware('App\Http\Middleware\JsonAuthenticate')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        
    Route::delete('/annotations/{id}', [App\Http\Controllers\ViewLegalTableController::class, 'deleteAnnotation'])
        ->name('api.annotations.delete')
        ->middleware('App\Http\Middleware\JsonAuthenticate')
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

require __DIR__.'/auth.php';
