<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\AdminAcademicController;
use App\Http\Controllers\Admin\AdminCatalogController;
use App\Http\Controllers\Admin\AdminCommunicationController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminModerationController;
use App\Http\Controllers\Admin\AdminOperationsController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminVendorController;
use App\Http\Controllers\AlumniDirectoryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DniLookupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ManualController;
use App\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

// Home Store-First, Web Informativa de Conversión & Portales de Instituciones
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/como-funciona', [HomeController::class, 'howItWorks'])->name('info.landing');
Route::get('/instituciones', [InstitutionController::class, 'index'])->name('institutions.index');
Route::get('/i/{institution:slug}', [InstitutionController::class, 'show'])->name('institutions.show');

// Tienda en línea, Carrito, Checkout y Seguimiento
Route::get('/tienda', [StoreController::class, 'index'])->name('store.index');
Route::get('/productos/{product:slug}', [StoreController::class, 'show'])->name('products.show');
Route::post('/productos/{product:slug}/wishlist', [StoreController::class, 'toggleWishlist'])->middleware('auth')->name('products.wishlist');
Route::post('/productos/{product:slug}/resena', [StoreController::class, 'storeReview'])->middleware('auth')->name('products.review');

Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/agregar', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/agregar-pack', [CartController::class, 'addPack'])->name('cart.add_pack');
Route::patch('/carrito/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/carrito/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/carrito/cupon', [CartController::class, 'applyCoupon'])->name('cart.coupon');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/pedido/{orderNumber}', [CheckoutController::class, 'confirmation'])->name('orders.confirmation');
Route::get('/pedido/{orderNumber}/comprobante', [CheckoutController::class, 'invoice'])->name('orders.invoice');
Route::post('/pedido/{orderNumber}/devolucion', [CheckoutController::class, 'storeReturn'])->name('orders.return.store');
Route::get('/seguimiento', [CheckoutController::class, 'track'])->name('orders.track');

// Blog y Noticias (Módulo L)
Route::get('/noticias', [BlogController::class, 'index'])->name('blog.index');
Route::get('/noticias/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Campañas Grupales
Route::get('/campanas', [CampaignController::class, 'index'])->name('campaigns.index');
Route::get('/campanas/{campaign:slug}', [CampaignController::class, 'show'])->name('campaigns.show');
Route::post('/campanas/{campaign:slug}/unirse', [CampaignController::class, 'join'])->name('campaigns.join');

// Directorio de Ex-alumnos
Route::get('/egresados', [AlumniDirectoryController::class, 'index'])->name('directory.index');
Route::post('/egresados', [AlumniDirectoryController::class, 'store'])->middleware('throttle:5,1')->name('directory.store');

// Comunidades (Institución -> Promoción -> Aula -> Interés)
Route::get('/comunidades', [CommunityController::class, 'index'])->name('communities.index');
Route::get('/comunidades/{community:slug}', [CommunityController::class, 'show'])->name('communities.show');
Route::middleware(['auth', 'active'])->group(function (): void {
    Route::post('/comunidades/{community:slug}/unirse', [CommunityController::class, 'join'])->name('communities.join');
    Route::post('/comunidades/{community:slug}/publicaciones', [CommunityController::class, 'storePost'])->name('communities.posts.store');
    Route::post('/publicaciones/{post}/like', [CommunityController::class, 'likePost'])->name('communities.posts.like');
    Route::post('/publicaciones/{post}/comentarios', [CommunityController::class, 'storeComment'])->name('communities.comments.store');
    Route::post('/publicaciones/{post}/reportar', [CommunityController::class, 'reportPost'])->name('communities.posts.report');
    Route::post('/comunidades/{community:slug}/eventos', [CommunityController::class, 'storeEvent'])->name('communities.events.store');
    Route::post('/eventos/{event}/rsvp', [CommunityController::class, 'rsvpEvent'])->name('communities.events.rsvp');
    Route::post('/encuestas/{poll}/votar', [CommunityController::class, 'votePoll'])->name('communities.polls.vote');
    Route::post('/comunidades/{community:slug}/colectas', [CommunityController::class, 'storeCollection'])->name('communities.collections.store');
    Route::post('/colectas/{collection}/aportar', [CommunityController::class, 'contributeCollection'])->name('communities.collections.contribute');
    Route::post('/comunidades/{community:slug}/albumes', [CommunityController::class, 'storeAlbum'])->name('communities.albums.store');
});

// Autenticación y Cuenta Personal
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'active'])->group(function (): void {
    Route::get('/mi-cuenta', [AuthController::class, 'account'])->name('account.index');
    Route::post('/mi-cuenta/privacidad', [AuthController::class, 'updatePrivacy'])->name('account.privacy');
    Route::post('/mi-cuenta/contrasena', [AuthController::class, 'updatePassword'])->name('account.password');
    Route::post('/mi-cuenta/notificaciones/leer', [AuthController::class, 'markNotificationsRead'])->name('account.notifications.read');

    // Manual de Uso en Línea y Versión PDF (exclusivo usuarios autenticados)
    Route::get('/manual', [ManualController::class, 'index'])->name('manual.index');
    Route::get('/manual/pdf', [ManualController::class, 'pdf'])->name('manual.pdf');
});

// Cumplimiento Legal Perú (Ley 29733 y Libro de Reclamaciones Virtual)
Route::get('/privacidad', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/terminos', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/libro-de-reclamaciones', [LegalController::class, 'complaints'])->name('legal.complaints');
Route::post('/libro-de-reclamaciones', [LegalController::class, 'storeComplaint'])->middleware('throttle:5,1')->name('legal.complaints.store');

// Consulta RENIEC por DNI vía apiperu.dev
Route::post('/api/dni/consultar', DniLookupController::class)->middleware('throttle:20,1')->name('dni.lookup');

// Panel de Administración por Rol
$adminRoles = implode(',', UserRole::backofficeRoles());

Route::prefix('admin')->name('admin.')->middleware(['auth', "role:{$adminRoles}"])->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/catalogo', [AdminCatalogController::class, 'index'])->name('catalog.index');
    Route::post('/catalogo/categorias', [AdminCatalogController::class, 'storeCategory'])->name('catalog.categories.store');
    Route::post('/catalogo/productos', [AdminCatalogController::class, 'storeProduct'])->name('catalog.products.store');
    Route::get('/catalogo/productos/{product:id}/editar', [AdminCatalogController::class, 'editProduct'])->name('catalog.products.edit');
    Route::put('/catalogo/productos/{product:id}', [AdminCatalogController::class, 'updateProduct'])->name('catalog.products.update');
    Route::patch('/catalogo/productos/{product:id}/estado', [AdminCatalogController::class, 'toggleProductStatus'])->name('catalog.products.toggle');
    Route::delete('/catalogo/productos/{product:id}', [AdminCatalogController::class, 'destroyProduct'])->name('catalog.products.destroy');
    Route::post('/catalogo/productos/{product:id}/variantes', [AdminCatalogController::class, 'storeVariant'])->name('catalog.variants.store');
    Route::patch('/catalogo/variantes/{variant}/stock', [AdminCatalogController::class, 'updateVariantStock'])->name('catalog.variants.stock');
    Route::patch('/catalogo/variantes/{variant}', [AdminCatalogController::class, 'updateVariant'])->name('catalog.variants.update');
    Route::delete('/catalogo/variantes/{variant}', [AdminCatalogController::class, 'destroyVariant'])->name('catalog.variants.destroy');
    Route::post('/catalogo/cupones', [AdminCatalogController::class, 'storeCoupon'])->name('catalog.coupons.store');
    Route::patch('/catalogo/cupones/{coupon}/estado', [AdminCatalogController::class, 'toggleCoupon'])->name('catalog.coupons.toggle');

    Route::get('/pedidos', [AdminOperationsController::class, 'orders'])->name('orders.index');
    Route::get('/pedidos/exportar-csv', [AdminOperationsController::class, 'exportOrdersCsv'])->name('orders.export');
    Route::patch('/pagos/{payment}/verificar', [AdminOperationsController::class, 'verifyPayment'])->name('payments.verify');
    Route::patch('/pedidos/{order}/estado', [AdminOperationsController::class, 'updateOrderStatus'])->name('orders.status');
    Route::post('/pedidos/{order}/nota-credito', [AdminOperationsController::class, 'issueCreditNote'])->name('orders.credit_note');
    Route::patch('/devoluciones/{returnRequest}', [AdminOperationsController::class, 'resolveReturn'])->name('returns.resolve');

    Route::get('/campanas', [AdminOperationsController::class, 'campaigns'])->name('campaigns.index');
    Route::post('/campanas', [AdminOperationsController::class, 'storeCampaign'])->name('campaigns.store');
    Route::patch('/campanas/{campaign}/estado', [AdminOperationsController::class, 'updateCampaignStatus'])->name('campaigns.status');
    Route::get('/campanas/{campaign:slug}/produccion', [AdminOperationsController::class, 'campaignProductionSheet'])->name('campaigns.production');

    Route::get('/instituciones', [AdminAcademicController::class, 'institutions'])->name('institutions.index');
    Route::post('/instituciones', [AdminAcademicController::class, 'storeInstitution'])->name('institutions.store');
    Route::post('/instituciones/{institution:slug}/delegados', [AdminAcademicController::class, 'storeDelegate'])->name('institutions.delegates.store');
    Route::patch('/delegados/{user}/estado', [AdminAcademicController::class, 'toggleDelegateStatus'])->name('delegates.toggle');
    Route::patch('/instituciones/{institution:slug}/publicacion', [AdminAcademicController::class, 'togglePublication'])->name('institutions.publication');
    Route::patch('/instituciones/{institution:slug}/apariencia', [AdminAcademicController::class, 'updateAppearance'])->name('institutions.appearance');
    Route::post('/promociones', [AdminAcademicController::class, 'storePromotion'])->name('promotions.store');
    Route::post('/aulas', [AdminAcademicController::class, 'storeClassroom'])->name('classrooms.store');

    Route::get('/egresados', [AdminAcademicController::class, 'alumni'])->name('alumni.index');
    Route::post('/egresados', [AdminAcademicController::class, 'storeAlumni'])->name('alumni.store');
    Route::patch('/egresados/{profile}/verificar', [AdminAcademicController::class, 'verifyAlumni'])->name('alumni.verify');
    Route::post('/egresados/importar-csv', [AdminAcademicController::class, 'importAlumniCsv'])->name('alumni.import');

    Route::get('/moderacion', [AdminModerationController::class, 'index'])->name('moderation.index');
    Route::patch('/moderacion/reportes/{report}', [AdminModerationController::class, 'resolveReport'])->name('moderation.reports.resolve');
    Route::patch('/moderacion/reclamaciones/{complaint}', [AdminModerationController::class, 'respondComplaint'])->name('moderation.complaints.respond');

    Route::get('/comunicaciones', [AdminCommunicationController::class, 'index'])->name('communications.index');
    Route::post('/comunicaciones/plantillas', [AdminCommunicationController::class, 'storeTemplate'])->name('communications.templates.store');
    Route::post('/comunicaciones/enviar', [AdminCommunicationController::class, 'broadcast'])->name('communications.broadcast');

    Route::get('/contenido', [AdminContentController::class, 'index'])->name('content.index');
    Route::post('/contenido/noticias', [AdminContentController::class, 'storePost'])->name('content.posts.store');
    Route::patch('/contenido/noticias/{post}/estado', [AdminContentController::class, 'togglePost'])->name('content.posts.toggle');
    Route::post('/contenido/banners', [AdminContentController::class, 'storeBanner'])->name('content.banners.store');
    Route::patch('/contenido/banners/{banner}/estado', [AdminContentController::class, 'toggleBanner'])->name('content.banners.toggle');

    // Módulos exclusivos de Superadmin: Usuarios/Roles, Proveedores/Liquidaciones y Reportes Gerenciales
    Route::middleware('role:'.UserRole::SuperAdmin->value)->group(function (): void {
        Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/usuarios', [AdminUserController::class, 'store'])->name('users.store');
        Route::patch('/usuarios/{user}', [AdminUserController::class, 'update'])->name('users.update');

        Route::get('/proveedores', [AdminVendorController::class, 'index'])->name('providers.index');
        Route::get('/vendedores', [AdminVendorController::class, 'index'])->name('vendors.index');
        Route::get('/vendedores/exportar-csv', [AdminVendorController::class, 'exportCsv'])->name('vendors.export');
        Route::post('/vendedores', [AdminVendorController::class, 'store'])->name('vendors.store');
        Route::patch('/vendedores/{vendor}', [AdminVendorController::class, 'update'])->name('vendors.update');
        Route::patch('/vendedores/{vendor}/verificar', [AdminVendorController::class, 'toggleVerified'])->name('vendors.verify');
        Route::patch('/vendedores/{vendor}/estado', [AdminVendorController::class, 'toggleStatus'])->name('vendors.toggle');
        Route::delete('/vendedores/{vendor}', [AdminVendorController::class, 'destroy'])->name('vendors.destroy');
        Route::post('/vendedores/{vendor}/liquidaciones', [AdminVendorController::class, 'createSettlement'])->name('vendors.settlements.store');
        Route::patch('/liquidaciones/{settlement}/pagar', [AdminVendorController::class, 'markSettlementPaid'])->name('settlements.pay');

        Route::get('/reportes', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/reportes/exportar/{report}', [AdminReportController::class, 'export'])->name('reports.export');

        Route::get('/configuracion', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::put('/configuracion', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/configuracion/probar-dni', [AdminSettingController::class, 'testDni'])->name('settings.test_dni');
        Route::post('/configuracion/backup', [AdminSettingController::class, 'backup'])->name('settings.backup');
        Route::get('/configuracion/backup/{filename}', [AdminSettingController::class, 'downloadSavedBackup'])->name('settings.backup.download');
        Route::post('/configuracion/blanquear', [AdminSettingController::class, 'wipe'])->name('settings.wipe');
        Route::post('/configuracion/datos-ejemplo', [AdminSettingController::class, 'seedDemo'])->name('settings.seed_demo');
    });
});
