<?php

use App\Enums\UserRole;
use App\Models\AlumniProfile;
use App\Models\BlogPost;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Community;
use App\Models\CommunityCollection;
use App\Models\CommunityPoll;
use App\Models\Institution;
use App\Models\Order;
use App\Models\Product;
use App\Models\ReturnRequest;
use App\Models\ShippingZone;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSettlement;
use App\Services\CartService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductionCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('renders public storefronts, institutions, campaigns, communities, and legal pages with JMSPERU branding and footer', function () {
    $footerText = 'Todos los derechos reservados son del Grupo JMSPERU :: Contacto 959199368';

    $urls = [
        '/',
        '/tienda',
        '/instituciones',
        '/i/guadalupe-lima',
        '/productos/casaca-varsity-guadalupe',
        '/campanas',
        '/campanas/casaca-promo-2006-guadalupe',
        '/egresados',
        '/comunidades',
        '/comunidades/guadalupe-promo-2006',
        '/seguimiento?codigo=PED-2026-0001',
        '/pedido/PED-2026-0001/comprobante',
        '/privacidad',
        '/terminos',
        '/libro-de-reclamaciones',
        '/login',
        '/registro',
    ];

    foreach ($urls as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('logo_jmsperu.png')
            ->assertSee($footerText);
    }
});

it('completes end-to-end cart, coupon, checkout, and SUNAT invoice generation', function () {
    $product = Product::with('variants')->where('slug', 'casaca-varsity-guadalupe')->firstOrFail();
    $variant = $product->variants->first();
    $zone = ShippingZone::where('rate', '>', 0)->firstOrFail();

    $this->post(route('cart.add'), [
        'product_id' => $product->id,
        'variant_id' => $variant->id,
        'quantity' => 2,
        'custom_institution' => 'Guadalupe',
        'custom_year' => '2006',
        'custom_name' => 'MORENO',
    ])->assertRedirect(route('cart.index'));

    $this->post(route('cart.coupon'), ['code' => 'PROMO10'])
        ->assertSessionHas('success');

    $response = $this->post(route('checkout.store'), [
        'customer_name' => 'Julio Moreno Prueba',
        'customer_email' => 'julio@example.pe',
        'customer_phone' => '959199368',
        'customer_dni_ruc' => '42158963',
        'document_type' => 'boleta',
        'shipping_zone_id' => $zone->id,
        'shipping_address' => 'Av. Arequipa 1234',
        'city' => 'Lima',
        'department' => 'Lima',
        'payment_method' => 'card_culqi',
    ]);

    $order = Order::where('customer_email', 'julio@example.pe')->firstOrFail();
    $response->assertRedirect(route('orders.confirmation', $order->order_number));

    expect($order->payment_status)->toBe('verified');
    expect($order->coupon_code)->toBe('PROMO10');
    expect($order->invoice)->not->toBeNull();
    expect($order->invoice->sunat_status)->toBe('accepted');

    $this->get('/api/v1/orders/'.$order->order_number.'/track')
        ->assertOk()
        ->assertJsonPath('order_number', $order->order_number);
});

it('allows joining a group campaign with deposit and updates campaign progress', function () {
    $campaign = Campaign::with('product.variants')->where('slug', 'casaca-promo-2006-guadalupe')->firstOrFail();
    $initialUnits = $campaign->current_units;
    $variant = $campaign->product->variants->first();

    $this->post(route('campaigns.join', $campaign->slug), [
        'participant_name' => 'Luis Torres',
        'participant_email' => 'luis@example.pe',
        'participant_phone' => '988776655',
        'variant_id' => $variant->id,
        'quantity' => 6,
        'custom_name' => 'TORRES',
        'custom_year' => '2006',
        'payment_plan' => 'deposit',
        'payment_method' => 'yape',
        'reference_code' => 'YAPE-998877',
    ])->assertRedirect();

    $campaign->refresh();
    expect($campaign->current_units)->toBe($initialUnits + 6);
    expect($campaign->status)->toBe('goal_reached');
});

it('handles alumni registration duplicate check, community interaction, and voting', function () {
    $guadalupe = Institution::where('slug', 'guadalupe-lima')->firstOrFail();

    // Duplicate DNI should be rejected cleanly
    $this->post(route('directory.store'), [
        'institution_id' => $guadalupe->id,
        'full_name' => 'Duplicado Prueba',
        'dni' => '43891234',
        'graduation_year' => 2006,
        'current_city' => 'Lima',
    ])->assertSessionHas('error');

    $alumni = User::where('email', 'egresado@jmsperu.pe')->firstOrFail();
    $community = Community::where('slug', 'guadalupe-promo-2006')->firstOrFail();
    $poll = CommunityPoll::where('community_id', $community->id)->with('options')->firstOrFail();

    $this->actingAs($alumni)
        ->post(route('communities.posts.store', $community->slug), [
            'category' => 'recuerdo',
            'title' => 'Foto promoción 2006',
            'body' => 'Qué buenos recuerdos del patio principal.',
        ])->assertSessionHas('success');

    $this->actingAs($alumni)
        ->post(route('communities.polls.vote', $poll->id), [
            'option_id' => $poll->options->first()->id,
        ])->assertSessionHas('success');
});

it('enforces role-based admin access and allows payment reconciliation plus CSV alumni import', function () {
    $buyer = User::where('email', 'comprador@jmsperu.pe')->firstOrFail();
    $admin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();

    $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();

    $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()
        ->assertSee('Todos los derechos reservados son del Grupo JMSPERU :: Contacto 959199368');

    $pendingOrder = Order::where('order_number', 'PED-2026-0002')->firstOrFail();
    $payment = $pendingOrder->payments()->firstOrFail();

    $this->actingAs($admin)->patch(route('admin.payments.verify', $payment->id), [
        'action' => 'approve',
    ])->assertSessionHas('success');

    $pendingOrder->refresh();
    expect($pendingOrder->payment_status)->toBe('verified');
    expect($pendingOrder->invoice)->not->toBeNull();

    $guadalupe = Institution::where('slug', 'guadalupe-lima')->firstOrFail();
    $csvContent = "nombre_completo,dni,anio_egreso,ciudad,email,profesion\nElena Rojas,71234567,2006,Lima,elena@example.pe,Economista\n";
    $file = UploadedFile::fake()->createWithContent('egresados.csv', $csvContent);

    $this->actingAs($admin)->post(route('admin.alumni.import'), [
        'institution_id' => $guadalupe->id,
        'csv_file' => $file,
    ])->assertSessionHas('success');

    expect(AlumniProfile::where('dni', '71234567')->exists())->toBeTrue();
});

it('registers a complaint in the Libro de Reclamaciones Virtual and exposes JSON APIs', function () {
    $this->post(route('legal.complaints.store'), [
        'customer_name' => 'Carlos Vega',
        'customer_doc_number' => '41234567',
        'customer_email' => 'carlos@example.pe',
        'customer_phone' => '999888777',
        'type' => 'reclamo',
        'subject' => 'Demora en envío a provincia',
        'detail' => 'El paquete demoró 2 días adicionales al plazo estimado.',
        'request_detail' => 'Solicito seguimiento prioritario con courier.',
    ])->assertRedirect();

    $this->get('/api/v1/institutions')->assertOk()->assertJsonStructure(['data']);
    $this->get('/api/v1/products')->assertOk()->assertJsonStructure(['data']);
    $this->get('/api/v1/campaigns')->assertOk()->assertJsonStructure(['data']);
});

it('displays published schools from Piura, Chiclayo, Trujillo, and Lima with PNG insignias and official colors', function () {
    $home = $this->get('/')->assertOk();

    foreach (['Piura', 'Chiclayo', 'Trujillo', 'Lima'] as $city) {
        $home->assertSee($city);
    }

    $published = Institution::published()->get();
    expect($published->count())->toBeGreaterThanOrEqual(12);

    foreach ($published as $inst) {
        expect($inst->yearsOfHistory())->toBeGreaterThan(10);
        expect($inst->logo_path)->toEndWith('.png');
        expect(file_exists(public_path(ltrim($inst->logo_path, '/'))))->toBeTrue();

        $home->assertSee($inst->name)
            ->assertSee($inst->primary_color)
            ->assertSee(ltrim($inst->logo_path, '/'));
    }

    // Unpublished schools must not be visible publicly
    $home->assertDontSee('Colegio Santa Ángela de Chiclayo');
    $this->get('/i/santa-angela-chiclayo')->assertNotFound();
});

it('requires an active delegate before superadmin can publish an institution to the main web', function () {
    $superAdmin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();
    $instAdmin = User::where('email', 'colegio@jmsperu.pe')->firstOrFail();
    $pendingSchool = Institution::where('slug', 'santa-angela-chiclayo')->firstOrFail();

    // Non-superadmin cannot publish institutions
    $this->actingAs($instAdmin)
        ->patch(route('admin.institutions.publication', $pendingSchool->slug), ['publish' => 1])
        ->assertForbidden();

    // Superadmin cannot publish without at least 1 active delegate
    $this->actingAs($superAdmin)
        ->patch(route('admin.institutions.publication', $pendingSchool->slug), ['publish' => 1])
        ->assertSessionHas('error');

    expect($pendingSchool->fresh()->is_published)->toBeFalse();

    // Superadmin gives alta to a delegate for Santa Ángela Chiclayo
    $this->actingAs($superAdmin)
        ->post(route('admin.institutions.delegates.store', $pendingSchool->slug), [
            'name' => 'Rosa Torres Chiclayo',
            'email' => 'delegado.santaangela@jmsperu.pe',
            'role' => 'promotion_delegate',
            'phone' => '979112233',
        ])->assertSessionHas('success');

    // Now superadmin can give alta to the school on the main web
    $this->actingAs($superAdmin)
        ->patch(route('admin.institutions.publication', $pendingSchool->slug), ['publish' => 1])
        ->assertSessionHas('success');

    expect($pendingSchool->fresh()->is_published)->toBeTrue();
    $this->get('/')->assertOk()->assertSee('Colegio Santa Ángela de Chiclayo');

    // Deactivating the sole delegate withdraws web publication automatically
    $delegate = User::where('email', 'delegado.santaangela@jmsperu.pe')->firstOrFail();
    $this->actingAs($superAdmin)
        ->patch(route('admin.delegates.toggle', $delegate->id))
        ->assertSessionHas('success');

    expect($pendingSchool->fresh()->is_published)->toBeFalse();

    // Creating a school with <= 10 years of history is rejected
    $this->actingAs($superAdmin)
        ->post(route('admin.institutions.store'), [
            'name' => 'Colegio Muy Nuevo',
            'short_name' => 'Nuevo',
            'type' => 'colegio',
            'city' => 'Lima',
            'region' => 'Lima',
            'founded_year' => (int) now()->year - 5,
            'primary_color' => '#1D70B8',
            'secondary_color' => '#F59E0B',
            'commission_rate' => 10,
        ])->assertSessionHasErrors('founded_year');
});

it('seeds all plan product types with real photos across every institution', function () {
    $products = Product::all();
    expect($products->count())->toBeGreaterThanOrEqual(45);

    foreach ($products as $product) {
        expect($product->image_path)->toStartWith('/images/products/');
        expect(file_exists(public_path(ltrim($product->image_path, '/'))))->toBeTrue();
    }

    foreach (Institution::published()->get() as $inst) {
        $schoolProductsCount = Product::where('institution_id', $inst->id)->count();
        expect($schoolProductsCount)->toBeGreaterThanOrEqual(3);

        $firstProduct = Product::where('institution_id', $inst->id)->firstOrFail();
        $this->get(route('institutions.show', $inst->slug))
            ->assertOk()
            ->assertSee($firstProduct->name);
    }
});

it('provides superadmin user/role management and marketplace vendor settlements', function () {
    $superAdmin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();
    $instAdmin = User::where('email', 'colegio@jmsperu.pe')->firstOrFail();

    // Non-superadmin cannot access users or vendors management
    $this->actingAs($instAdmin)->get(route('admin.users.index'))->assertForbidden();
    $this->actingAs($instAdmin)->get(route('admin.vendors.index'))->assertForbidden();

    // Superadmin creates user with role & updates role
    $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk();
    $this->actingAs($superAdmin)->post(route('admin.users.store'), [
        'name' => 'Nuevo Delegado Piura',
        'email' => 'nuevo.delegado@jmsperu.pe',
        'password' => 'secret123',
        'role' => UserRole::PromotionDelegate->value,
        'institution_id' => Institution::where('slug', 'salesiano-don-bosco-piura')->firstOrFail()->id,
        'city' => 'Piura',
        'is_active' => 1,
    ])->assertSessionHas('success');

    $createdUser = User::where('email', 'nuevo.delegado@jmsperu.pe')->firstOrFail();
    expect($createdUser->role)->toBe(UserRole::PromotionDelegate->value);

    $this->actingAs($superAdmin)->patch(route('admin.users.update', $createdUser->id), [
        'name' => 'Nuevo Delegado Piura Actualizado',
        'role' => UserRole::InstitutionAdmin->value,
        'institution_id' => $createdUser->institution_id,
        'is_active' => 1,
    ])->assertSessionHas('success');

    expect($createdUser->fresh()->role)->toBe(UserRole::InstitutionAdmin->value);

    // Superadmin cannot self-demote
    $this->actingAs($superAdmin)->patch(route('admin.users.update', $superAdmin->id), [
        'name' => $superAdmin->name,
        'role' => UserRole::Buyer->value,
        'is_active' => 1,
    ])->assertSessionHas('error');

    // Vendor management and liquidation settlement
    $this->actingAs($superAdmin)->get(route('admin.vendors.index'))->assertOk();
    $vendor = Vendor::firstOrFail();
    $this->actingAs($superAdmin)->post(route('admin.vendors.settlements.store', $vendor->id), [
        'period_label' => '2026-11',
        'notes' => 'Corte mensual',
    ])->assertSessionHas('success');

    $settlement = VendorSettlement::where('period_label', '2026-11')->firstOrFail();
    $this->actingAs($superAdmin)->patch(route('admin.settlements.pay', $settlement->id), [
        'reference_code' => 'BCP-998123',
    ])->assertSessionHas('success');
    expect($settlement->fresh()->status)->toBe('paid');
});

it('supports communications, blog/banners, referrals, collections, production sheet, and SUNAT credit notes', function () {
    $superAdmin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();
    $alumni = User::where('email', 'egresado@jmsperu.pe')->firstOrFail();

    // Public blog & campaign production sheet
    $this->get('/noticias')->assertOk();
    $post = BlogPost::firstOrFail();
    $this->get('/noticias/'.$post->slug)->assertOk()->assertSee($post->title);

    $campaign = Campaign::firstOrFail();
    $this->actingAs($superAdmin)->get(route('admin.campaigns.production', $campaign->slug))
        ->assertOk()
        ->assertSee('Orden de Producción Consolidada');

    // Communications broadcast & user notifications
    $this->actingAs($superAdmin)->post(route('admin.communications.broadcast'), [
        'title' => 'Aviso de Cierre de Campaña',
        'body' => 'Últimas 48 horas para participar.',
        'channel' => 'web',
    ])->assertSessionHas('success');

    $this->actingAs($alumni)->get(route('account.index'))->assertOk()->assertSee('Aviso de Cierre de Campaña');
    $this->actingAs($alumni)->post(route('account.notifications.read'))->assertSessionHas('success');

    // Community collection contribution
    $collection = CommunityCollection::firstOrFail();
    $before = (float) $collection->current_amount;
    $this->actingAs($alumni)->post(route('communities.collections.contribute', $collection->id), [
        'amount' => 75,
        'reference_code' => 'YAPE-COLECTA-1',
    ])->assertSessionHas('success');
    expect((float) $collection->fresh()->current_amount)->toBe($before + 75);

    // Return request & automatic SUNAT Nota de Crédito
    $order = Order::where('order_number', 'PED-2026-0001')->firstOrFail();
    $this->actingAs($alumni)->post(route('orders.return.store', $order->order_number), [
        'request_type' => 'devolucion',
        'reason' => 'Talla incorrecta recibida',
    ])->assertSessionHas('success');

    $ret = ReturnRequest::where('order_id', $order->id)->firstOrFail();
    $this->actingAs($superAdmin)->patch(route('admin.returns.resolve', $ret->id), [
        'status' => 'approved',
        'admin_notes' => 'Procede nota de crédito.',
    ])->assertSessionHas('success');

    expect($order->invoices()->where('document_type', 'nota_credito')->exists())->toBeTrue();

    // Referral registration tracking
    $this->post(route('register.store'), [
        'name' => 'Referido Prueba',
        'email' => 'referido@example.pe',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
        'referral_code_used' => 'PROMO06L',
        'privacy_consent' => '1',
    ])->assertRedirect(route('account.index'));

    $referred = User::where('email', 'referido@example.pe')->firstOrFail();
    expect($referred->referredBy?->email)->toBe('delegado@jmsperu.pe');
});

it('provides full catalog CRUD with purchase and sale prices, client verified vendor vs superadmin vendor name', function () {
    $superAdmin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();
    $vendor = Vendor::where('is_verified', true)->firstOrFail();
    $category = Category::firstOrFail();

    // Superadmin sees vendor name, purchase price and sale price in admin catalog
    $this->actingAs($superAdmin)->get(route('admin.catalog.index'))
        ->assertOk()
        ->assertSee($vendor->business_name)
        ->assertSee('Compra:')
        ->assertSee('Venta Público:');

    // Client storefront shows "Proveedor Verificado" badge instead of exposing vendor legal business_name
    $casaca = Product::where('slug', 'casaca-varsity-guadalupe')->firstOrFail();
    $this->get(route('products.show', $casaca->slug))
        ->assertOk()
        ->assertSee('Proveedor Verificado')
        ->assertDontSee($vendor->business_name);

    $this->get('/tienda?verificado=1')
        ->assertOk()
        ->assertSee('Proveedor Verificado');

    // Create product with purchase_price & base_price
    $this->actingAs($superAdmin)->post(route('admin.catalog.products.store'), [
        'name' => 'Polera Experimental CRUD',
        'sku_prefix' => 'EXP-01',
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
        'purchase_price' => 45.00,
        'base_price' => 95.00,
        'member_price' => 80.00,
        'short_description' => 'Resumen de prueba CRUD',
        'description' => 'Descripción completa de prueba CRUD',
        'image_preset' => '/images/products/polera.jpg',
        'variant_size' => 'M',
        'variant_color' => 'Azul',
        'variant_stock' => 15,
        'is_customizable' => 1,
        'show_verified_vendor' => 1,
    ])->assertSessionHas('success');

    $created = Product::where('sku_prefix', 'EXP-01')->firstOrFail();
    expect((float) $created->purchase_price)->toBe(45.00);
    expect((float) $created->base_price)->toBe(95.00);
    expect($created->profitAmount())->toBe(50.00);

    // Edit and update product
    $this->actingAs($superAdmin)->get(route('admin.catalog.products.edit', $created->id))->assertOk();
    $this->actingAs($superAdmin)->put(route('admin.catalog.products.update', $created->id), [
        'name' => 'Polera Experimental Editada',
        'sku_prefix' => 'EXP-01',
        'category_id' => $category->id,
        'vendor_id' => $vendor->id,
        'purchase_price' => 50.00,
        'base_price' => 110.00,
        'member_price' => 92.00,
        'short_description' => 'Resumen actualizado',
        'description' => 'Descripción actualizada',
        'image_preset' => '/images/products/polera.jpg',
        'is_active' => 1,
        'show_verified_vendor' => 0,
    ])->assertSessionHas('success');

    expect((float) $created->fresh()->purchase_price)->toBe(50.00);
    expect($created->fresh()->hasVerifiedVendorBadge())->toBeFalse();

    // Add variant, toggle active, and delete unused product
    $this->actingAs($superAdmin)->post(route('admin.catalog.variants.store', $created->id), [
        'sku' => 'EXP-01-L-AZU',
        'size' => 'L',
        'color' => 'Azul',
        'stock' => 12,
    ])->assertSessionHas('success');
    expect($created->variants()->count())->toBe(2);

    $this->actingAs($superAdmin)->patch(route('admin.catalog.products.toggle', $created->id))->assertSessionHas('success');
    expect($created->fresh()->is_active)->toBeFalse();

    $this->actingAs($superAdmin)->delete(route('admin.catalog.products.destroy', $created->id))->assertRedirect(route('admin.catalog.index'));
    expect(Product::find($created->id))->toBeNull();
});

it('provides complete supplier management with verification and 10 executive reports for superadmin', function () {
    $superAdmin = User::where('email', 'admin@jmsperu.pe')->firstOrFail();
    $instAdmin = User::where('email', 'colegio@jmsperu.pe')->firstOrFail();

    // Non-superadmin denied on reports and providers
    $this->actingAs($instAdmin)->get(route('admin.reports.index'))->assertForbidden();
    $this->actingAs($instAdmin)->get(route('admin.providers.index'))->assertForbidden();

    // Superadmin creates, updates, and verifies supplier
    $this->actingAs($superAdmin)->post(route('admin.vendors.store'), [
        'business_name' => 'Talleres Unidos Piura SAC',
        'trade_name' => 'Talleres Piura',
        'ruc' => '20604455667',
        'city' => 'Piura',
        'phone' => '969887766',
        'contact_name' => 'Jorge Alarcón',
        'email' => 'talleres@piura.pe',
        'category_specialty' => 'Polos Pima y Bordados',
        'bank_name' => 'BCP',
        'bank_cci' => '00219100998877665544',
        'commission_rate' => 11,
        'lead_time_days' => 5,
    ])->assertSessionHas('success');

    $newVendor = Vendor::where('ruc', '20604455667')->firstOrFail();
    $this->actingAs($superAdmin)->patch(route('admin.vendors.verify', $newVendor->id))->assertSessionHas('success');
    expect($newVendor->fresh()->is_verified)->toBeFalse();

    $this->actingAs($superAdmin)->patch(route('admin.vendors.verify', $newVendor->id))->assertSessionHas('success');
    expect($newVendor->fresh()->is_verified)->toBeTrue();

    $this->actingAs($superAdmin)->get(route('admin.vendors.export'))->assertOk();

    // 10 Executive Reports for Superadmin
    $reports = $this->actingAs($superAdmin)->get(route('admin.reports.index'))->assertOk();
    foreach (['01', '02', '03', '04', '05', '06', '07', '08', '09', '10'] as $num) {
        $reports->assertSee("Reporte Gerencial {$num}");
    }

    $this->actingAs($superAdmin)->get(route('admin.reports.export', ['report' => 'r2-rentabilidad']))->assertOk();
});

it('loads store-first homepage, conversion landing, express registration, packs, volume discounts, and checkout signup', function () {
    // Homepage loads store-first with catalog products & published schools
    $this->get('/')->assertOk()
        ->assertSee('Catálogo Oficial Personalizable')
        ->assertSee('PROMO10')
        ->assertSee('+ Agregar')
        ->assertSee('Pack Reencuentro Total');

    // Persuasive conversion landing (/como-funciona)
    $this->get(route('info.landing'))->assertOk()
        ->assertSee('Regístrate en 15s')
        ->assertSee('Tarifa Ex-Alumno');

    // Express 3-field registration (no password_confirmation required) + redirect_to
    $this->post(route('register.store'), [
        'name' => 'Cliente Express',
        'email' => 'express@example.pe',
        'password' => 'secret123',
        'privacy_consent' => '1',
        'redirect_to' => '/tienda',
    ])->assertRedirect('/tienda');

    auth()->logout();

    // Volume discount (12+ uds => -10%) and direct buy_now redirect to checkout
    $gorro = Product::with('variants')->where('slug', 'gorro-drill-bordado-personalizable')->firstOrFail();
    $gorroVariant = $gorro->variants->first();

    $this->post(route('cart.add'), [
        'product_id' => $gorro->id,
        'variant_id' => $gorroVariant->id,
        'quantity' => 12,
        'buy_now' => 1,
    ])->assertRedirect(route('checkout.index'));

    $cart = app(CartService::class)->items();
    $firstItem = collect($cart)->first();
    expect($firstItem['volume_discount_percent'])->toBe(10);
    expect((float) $firstItem['unit_price'])->toBe(round((float) $gorro->base_price * 0.90, 2));

    // Checkout 1-click account creation for guest buyer
    $zone = ShippingZone::firstOrFail();
    $this->post(route('checkout.store'), [
        'customer_name' => 'Comprador Express SAC',
        'customer_email' => 'nuevo_checkout@example.pe',
        'customer_phone' => '987111222',
        'customer_dni_ruc' => '44556611',
        'document_type' => 'boleta',
        'shipping_zone_id' => $zone->id,
        'shipping_address' => 'Av. Pardo 500, Miraflores',
        'city' => 'Lima',
        'department' => 'Lima',
        'payment_method' => 'yape',
        'reference_code' => 'YAPE-EXP-01',
        'create_account' => '1',
        'account_password' => 'clave123',
    ])->assertRedirect();

    $newAccount = User::where('email', 'nuevo_checkout@example.pe')->firstOrFail();
    $newOrder = Order::where('customer_email', 'nuevo_checkout@example.pe')->firstOrFail();
    expect($newOrder->user_id)->toBe($newAccount->id);

    // 1-click Combo Pack adds products and applies coupon
    auth()->logout();
    $this->post(route('cart.add_pack'), ['bundle_code' => 'kit-promo'])
        ->assertRedirect(route('cart.index'));
    expect(app(CartService::class)->count())->toBe(3);
    $this->get(route('cart.index'))->assertOk()->assertSee('Completa tu pedido');
});

it('serves expanded white-background jewelry & souvenirs, WhatsApp links, and hardens user security without demo logins', function () {
    // 1. Catálogo ampliado: anillos, cadenas, medallas, mochilas, libretas y copas + pack gala
    foreach (['anillo.jpg', 'cadena.jpg', 'medalla.jpg', 'mochila.jpg', 'libreta.jpg', 'copa.jpg'] as $img) {
        expect(file_exists(public_path("images/products/{$img}")))->toBeTrue();
    }

    $this->get('/tienda?categoria=anillos-cadenas-medallas')
        ->assertOk()
        ->assertSee('Anillo de Promoción')
        ->assertSee('Cadena con Dije')
        ->assertSee('Medalla Conmemorativa');

    $this->get('/tienda?categoria=mochilas-brindis')
        ->assertOk()
        ->assertSee('Mochila Ejecutiva')
        ->assertSee('Copa de Cristal');

    $this->get('/tienda?q=Libreta')
        ->assertOk()
        ->assertSee('Libreta');

    $this->post(route('cart.add_pack'), ['bundle_code' => 'kit-gala'])
        ->assertRedirect(route('cart.index'));
    expect(app(CartService::class)->count())->toBe(3);

    // 2. Contacto 959199368 enlazado a WhatsApp y cabeceras de seguridad
    $home = $this->get('/')->assertOk();
    $home->assertSee('https://wa.me/51959199368', false)
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff');

    // 3. Login limpio sin usuarios demo ni ruta backdoor
    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('Acceso rápido (Usuarios Demo)')
        ->assertDontSee('admin@jmsperu.pe')
        ->assertDontSee('superadmin@mipromo.pe');

    expect(Route::has('login.demo'))->toBeFalse();
    $this->post('/login/demo', ['email' => 'superadmin@mipromo.pe'])->assertStatus(404);

    // 4. Registro exige contraseña segura (letras + números >= 8) y normaliza email
    $this->post(route('register.store'), [
        'name' => 'Clave Débil',
        'email' => 'weak@example.pe',
        'password' => '123456',
        'privacy_consent' => '1',
    ])->assertSessionHasErrors('password');

    $this->post(route('register.store'), [
        'name' => 'Cliente Seguro',
        'email' => '  CLIENTE.SEGURO@EXAMPLE.PE ',
        'password' => 'MiPromo2026',
        'privacy_consent' => '1',
    ])->assertRedirect(route('account.index'));

    $createdUser = User::where('email', 'cliente.seguro@example.pe')->firstOrFail();
    expect($createdUser->privacy_consent_at)->not->toBeNull();

    // 5. Cambio de contraseña validando contraseña actual
    $this->actingAs($createdUser)->post(route('account.password'), [
        'current_password' => 'MiPromo2026',
        'password' => 'NuevaClave2026',
        'password_confirmation' => 'NuevaClave2026',
    ])->assertRedirect();

    expect(Hash::check('NuevaClave2026', $createdUser->fresh()->password))->toBeTrue();

    // 6. Usuario suspendido (is_active = false) queda bloqueado en rutas protegidas y login
    $createdUser->update(['is_active' => false]);
    $this->actingAs($createdUser->fresh())->get(route('account.index'))->assertRedirect(route('login'));

    auth()->logout();
    $this->post(route('login.attempt'), [
        'email' => 'cliente.seguro@example.pe',
        'password' => 'NuevaClave2026',
    ])->assertSessionHas('error');

    // 7. Checkout como invitado con email de un tercero existente NO vincula el pedido al tercero
    $existingSuper = User::where('role', UserRole::SuperAdmin->value)->firstOrFail();
    $product = Product::with('variants')->firstOrFail();
    $zone = ShippingZone::firstOrFail();

    $this->post(route('cart.add'), [
        'product_id' => $product->id,
        'variant_id' => $product->variants->first()->id,
        'quantity' => 1,
    ]);

    $this->post(route('checkout.store'), [
        'customer_name' => 'Intento Tercero',
        'customer_email' => $existingSuper->email,
        'customer_phone' => '999000111',
        'customer_dni_ruc' => '12345678',
        'document_type' => 'boleta',
        'shipping_zone_id' => $zone->id,
        'shipping_address' => 'Jr. Lima 100',
        'city' => 'Lima',
        'department' => 'Lima',
        'payment_method' => 'yape',
        'reference_code' => 'YAPE-SEC-01',
        'create_account' => '1',
        'account_password' => 'HackPass2026',
    ])->assertRedirect();

    $thirdOrder = Order::where('customer_name', 'Intento Tercero')->firstOrFail();
    expect($thirdOrder->user_id)->toBeNull();

    // 8. API pública oculta costos internos (purchase_price) y datos bancarios de proveedores
    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertDontSee('purchase_price');
});

it('seeds clean production catalog without demo password users via ProductionCatalogSeeder', function () {
    DB::statement('PRAGMA foreign_keys = OFF');
    foreach (['order_items', 'payments', 'invoices', 'orders', 'product_variants', 'products', 'categories', 'vendors', 'coupons', 'shipping_zones', 'promotional_banners', 'blog_posts', 'message_templates', 'institutions', 'users'] as $table) {
        DB::table($table)->delete();
    }
    DB::statement('PRAGMA foreign_keys = ON');

    $admin = User::create([
        'name' => 'Admin Real',
        'email' => 'gerencia@jmsperu.com',
        'password' => Hash::make('JmsPeruProd2026!'),
        'role' => UserRole::SuperAdmin->value,
        'referral_code' => 'JMSPROD1',
        'is_active' => true,
        'privacy_consent_at' => now(),
    ]);

    $this->seed(ProductionCatalogSeeder::class);

    expect(User::count())->toBe(1);
    expect(User::where('email', 'superadmin@mipromo.pe')->exists())->toBeFalse();
    expect(Product::count())->toBeGreaterThanOrEqual(40);
    expect(Category::count())->toBe(9);
    expect(Vendor::count())->toBe(2);
    expect(Institution::count())->toBe(15);
    expect($admin->fresh()->role)->toBe(UserRole::SuperAdmin->value);
});

it('configures encrypted apiperu.dev token, queries and registers clients/alumni by DNI, and generates Online & PDF User Manual', function () {
    $superAdmin = User::where('role', UserRole::SuperAdmin->value)->firstOrFail();

    // 1. Solo Superadmin accede al módulo de configuración y puede guardar token cifrado
    $nonAdmin = User::where('role', UserRole::Buyer->value)->firstOrFail();
    $this->actingAs($nonAdmin)->get(route('admin.settings.index'))->assertForbidden();

    $plainToken = 'ap_live_tok_9876543210secret';
    $this->actingAs($superAdmin)->put(route('admin.settings.update'), [
        'apiperu_enabled' => '1',
        'apiperu_endpoint' => 'https://api.apiperu.dev/dni',
        'apiperu_token' => $plainToken,
        'whatsapp_number' => '959199368',
        'support_email' => 'soporte@jmsperu.com',
    ])->assertRedirect();

    $rawDbRow = SystemSetting::where('key', 'apiperu_token')->firstOrFail();
    expect($rawDbRow->is_encrypted)->toBeTrue();
    expect($rawDbRow->value)->not->toBe($plainToken);
    expect(SystemSetting::getValue('apiperu_token'))->toBe($plainToken);

    $this->actingAs($superAdmin)->get(route('admin.settings.index'))
        ->assertOk()
        ->assertDontSee($plainToken)
        ->assertSee('Activa ✓');

    // 2. Consulta DNI real contra apiperu.dev (mock HTTP exacto según contrato POST Bearer)
    Http::fake([
        'https://api.apiperu.dev/dni' => Http::response([
            'success' => true,
            'code' => 'found',
            'data' => [
                'numero' => '73291045',
                'nombres' => 'CARLOS ALBERTO',
                'apellido_paterno' => 'QUISPE',
                'apellido_materno' => 'MENDOZA',
                'nombre_completo' => 'QUISPE MENDOZA CARLOS ALBERTO',
                'codigo_verificacion' => 4,
            ],
        ], 200),
    ]);

    auth()->logout();

    $this->postJson(route('dni.lookup'), ['dni' => '73291045'])
        ->assertOk()
        ->assertJsonPath('ok', true)
        ->assertJsonPath('data.dni', '73291045')
        ->assertJsonPath('data.full_name', 'Carlos Alberto Quispe Mendoza')
        ->assertJsonMissingPath('local_matches');

    // 3. Registro de cliente con DNI y búsqueda en Directorio/Admin por DNI
    $this->post(route('register.store'), [
        'dni' => '73291045',
        'name' => 'Carlos Alberto Quispe Mendoza',
        'email' => 'carlos.dni@example.pe',
        'password' => 'Carlos2026!',
        'privacy_consent' => '1',
    ])->assertRedirect(route('account.index'));

    $dniBuyer = User::where('email', 'carlos.dni@example.pe')->firstOrFail();
    expect($dniBuyer->dni)->toBe('73291045');

    // Alta individual de ex-alumno por DNI en /admin/egresados (eleva cuenta existente al validar)
    $institution = Institution::firstOrFail();
    $this->actingAs($superAdmin)->post(route('admin.alumni.store'), [
        'institution_id' => $institution->id,
        'dni' => '73291045',
        'full_name' => 'Carlos Alberto Quispe Mendoza',
        'graduation_year' => 2012,
        'current_city' => 'Piura',
        'profession' => 'Ingeniero Industrial',
        'auto_verify' => '1',
    ])->assertRedirect();

    expect($dniBuyer->fresh()->role)->toBe(UserRole::Alumni->value);

    // Búsqueda de ex-alumnos por DNI en directorio público y panel admin
    $this->get(route('directory.index', ['q' => '73291045']))
        ->assertOk()
        ->assertSee('Carlos Alberto Quispe Mendoza');

    $this->actingAs($superAdmin)->get(route('admin.alumni.index', ['q' => '73291045']))
        ->assertOk()
        ->assertSee('73291045')
        ->assertSee('Carlos Alberto Quispe Mendoza');

    // Alta de usuario desde Superadmin requiere contraseña fuerte y guarda DNI
    $this->actingAs($superAdmin)->post(route('admin.users.store'), [
        'dni' => '44556688',
        'name' => 'Delegado ApiPeru',
        'email' => 'delegado.dni@example.pe',
        'password' => 'Delegado2026',
        'role' => UserRole::PromotionDelegate->value,
        'institution_id' => $institution->id,
        'is_active' => '1',
    ])->assertRedirect();

    expect(User::where('email', 'delegado.dni@example.pe')->value('dni'))->toBe('44556688');

    // 4. Manual de Uso en Línea y Versión en PDF con capturas reales del sistema
    auth()->logout();
    $manualHtml = $this->get(route('manual.index'))
        ->assertOk()
        ->assertSee('Manual de Uso Interactivo')
        ->assertSee('apiperu.dev')
        ->assertSee('01-home-tienda.jpg')
        ->assertSee('17-admin-configuracion-token.jpg');

    $pdfResponse = $this->get(route('manual.pdf'))->assertOk();
    expect($pdfResponse->headers->get('content-type'))->toContain('application/pdf');
    expect(str_starts_with((string) $pdfResponse->getContent(), '%PDF-'))->toBeTrue();
});
