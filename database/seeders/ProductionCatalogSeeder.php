<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Institution;
use App\Models\MessageTemplate;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PromotionalBanner;
use App\Models\ShippingZone;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder limpio de producción para instalador en BanaHosting.
 * Crea colegios históricos, proveedores homologados, catálogo completo con fotos reales en fondo blanco,
 * categorías, cupones y zonas de envío SIN crear cuentas demo con contraseñas débiles.
 */
class ProductionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (Category::exists() || Product::exists()) {
            return;
        }

        $superAdmin = User::where('role', UserRole::SuperAdmin->value)->first();

        $institutionsData = [
            ['name' => 'I.E. Emblemática Nuestra Señora de Guadalupe', 'slug' => 'guadalupe-lima', 'short_name' => 'Guadalupe', 'city' => 'Lima', 'region' => 'Lima Metropolitana', 'founded_year' => 1840, 'motto' => 'Juventud, Estudio y Patria', 'primary_color' => '#B91C1C', 'secondary_color' => '#EAB308', 'logo_path' => '/images/insignias/guadalupe-lima.png', 'published' => true],
            ['name' => 'I.E. Emblemática Alfonso Ugarte', 'slug' => 'alfonso-ugarte-lima', 'short_name' => 'Alfonso Ugarte', 'city' => 'Lima', 'region' => 'Lima Metropolitana', 'founded_year' => 1928, 'motto' => 'Voluntad, Disciplina y Acción', 'primary_color' => '#6B0F2B', 'secondary_color' => '#FACC15', 'logo_path' => '/images/insignias/alfonso-ugarte-lima.png', 'published' => true],
            ['name' => 'I.E. Emblemática Melitón Carvajal', 'slug' => 'meliton-carvajal-lima', 'short_name' => 'Melitón Carvajal', 'city' => 'Lima', 'region' => 'Lima Metropolitana', 'founded_year' => 1948, 'motto' => 'Claridad en la mente y energía en el espíritu', 'primary_color' => '#DC2626', 'secondary_color' => '#0F2D4A', 'logo_path' => '/images/insignias/meliton-carvajal-lima.png', 'published' => true],
            ['name' => 'Colegio Salesiano Don Bosco de Piura', 'slug' => 'salesiano-don-bosco-piura', 'short_name' => 'Salesiano Piura', 'city' => 'Piura', 'region' => 'Piura', 'founded_year' => 1906, 'motto' => 'Buenos cristianos y honrados ciudadanos', 'primary_color' => '#C81E1E', 'secondary_color' => '#F59E0B', 'logo_path' => '/images/insignias/salesiano-don-bosco-piura.png', 'published' => true],
            ['name' => 'Colegio San Ignacio de Loyola de Piura', 'slug' => 'san-ignacio-piura', 'short_name' => 'San Ignacio Piura', 'city' => 'Piura', 'region' => 'Piura', 'founded_year' => 1958, 'motto' => 'En todo amar y servir', 'primary_color' => '#1E3A8A', 'secondary_color' => '#B4892C', 'logo_path' => '/images/insignias/san-ignacio-piura.png', 'published' => true],
            ['name' => 'Colegio Santa María de Piura', 'slug' => 'santa-maria-piura', 'short_name' => 'Santa María Piura', 'city' => 'Piura', 'region' => 'Piura', 'founded_year' => 1962, 'motto' => 'Ciencia, Virtud y Acción', 'primary_color' => '#1E296B', 'secondary_color' => '#3B82F6', 'logo_path' => '/images/insignias/santa-maria-piura.png', 'published' => true],
            ['name' => 'Colegio Nacional de San José de Chiclayo', 'slug' => 'san-jose-chiclayo', 'short_name' => 'San José Chiclayo', 'city' => 'Chiclayo', 'region' => 'Lambayeque', 'founded_year' => 1859, 'motto' => 'Perseverancia y Fraternidad Lambayecana', 'primary_color' => '#7F1D1D', 'secondary_color' => '#EAB308', 'logo_path' => '/images/insignias/san-jose-chiclayo.png', 'published' => true],
            ['name' => 'Colegio Manuel Pardo de Chiclayo', 'slug' => 'manuel-pardo-chiclayo', 'short_name' => 'Manuel Pardo', 'city' => 'Chiclayo', 'region' => 'Lambayeque', 'founded_year' => 1917, 'motto' => '¡Amar, Evangelizar, Saber!', 'primary_color' => '#047857', 'secondary_color' => '#F59E0B', 'logo_path' => '/images/insignias/manuel-pardo-chiclayo.png', 'published' => true],
            ['name' => 'Colegio San Agustín de Chiclayo', 'slug' => 'san-agustin-chiclayo', 'short_name' => 'San Agustín Chiclayo', 'city' => 'Chiclayo', 'region' => 'Lambayeque', 'founded_year' => 1966, 'motto' => 'Cor unum in Deum', 'primary_color' => '#0F294A', 'secondary_color' => '#DC2626', 'logo_path' => '/images/insignias/san-agustin-chiclayo.png', 'published' => true],
            ['name' => 'Colegio Santa Ángela de Chiclayo', 'slug' => 'santa-angela-chiclayo', 'short_name' => 'Santa Ángela', 'city' => 'Chiclayo', 'region' => 'Lambayeque', 'founded_year' => 1962, 'motto' => 'Serviam — Fe, Ciencia y Servicio', 'primary_color' => '#1E3A8A', 'secondary_color' => '#64748B', 'logo_path' => '/images/insignias/santa-angela-chiclayo.png', 'published' => false],
            ['name' => 'I.E. Emblemática Santa Rosa de Trujillo', 'slug' => 'santa-rosa-trujillo', 'short_name' => 'Santa Rosa Trujillo', 'city' => 'Trujillo', 'region' => 'La Libertad', 'founded_year' => 1898, 'motto' => 'Luz y Verdad', 'primary_color' => '#18181B', 'secondary_color' => '#F59E0B', 'logo_path' => '/images/insignias/santa-rosa-trujillo.png', 'published' => true],
            ['name' => 'Colegio Claretiano de Trujillo', 'slug' => 'claretiano-trujillo', 'short_name' => 'Claretiano Trujillo', 'city' => 'Trujillo', 'region' => 'La Libertad', 'founded_year' => 1960, 'motto' => 'Educar en la fe, ciencia y valores', 'primary_color' => '#0B5394', 'secondary_color' => '#F59E0B', 'logo_path' => '/images/insignias/claretiano-trujillo.png', 'published' => true],
            ['name' => 'Colegio San José Obrero Marianistas de Trujillo', 'slug' => 'san-jose-obrero-trujillo', 'short_name' => 'San José Obrero', 'city' => 'Trujillo', 'region' => 'La Libertad', 'founded_year' => 1957, 'motto' => 'Familia, Fe y Servicio', 'primary_color' => '#0E5A75', 'secondary_color' => '#9F2B48', 'logo_path' => '/images/insignias/san-jose-obrero-trujillo.png', 'published' => false],
            ['name' => 'Colegio Nacional de Ciencias del Cusco', 'slug' => 'ciencias-cusco', 'short_name' => 'Ciencias Cusco', 'city' => 'Cusco', 'region' => 'Cusco', 'founded_year' => 1825, 'motto' => 'Ciencia, Honor y Tradición Imperial', 'primary_color' => '#1E2460', 'secondary_color' => '#94A3B8', 'logo_path' => '/images/insignias/ciencias-cusco.png', 'published' => true],
            ['name' => 'I.E. Independencia Americana de Arequipa', 'slug' => 'independencia-arequipa', 'short_name' => 'Independencia AQP', 'city' => 'Arequipa', 'region' => 'Arequipa', 'founded_year' => 1827, 'motto' => 'Lealtad, Cultura y Progreso', 'primary_color' => '#B91C1C', 'secondary_color' => '#0284C7', 'logo_path' => '/images/insignias/independencia-arequipa.png', 'published' => true],
        ];

        $institutions = [];
        foreach ($institutionsData as $row) {
            $institutions[] = Institution::create([
                'name' => $row['name'],
                'slug' => $row['slug'],
                'short_name' => $row['short_name'],
                'type' => 'colegio',
                'city' => $row['city'],
                'region' => $row['region'],
                'founded_year' => $row['founded_year'],
                'motto' => $row['motto'],
                'history' => "Institución educativa histórica de {$row['city']} con {$row['founded_year']} como año de fundación.",
                'primary_color' => $row['primary_color'],
                'secondary_color' => $row['secondary_color'],
                'logo_path' => $row['logo_path'],
                'commission_rate' => 10.00,
                'is_featured' => $row['published'],
                'is_published' => $row['published'],
                'approved_by' => $row['published'] ? $superAdmin?->id : null,
                'approved_at' => $row['published'] ? now() : null,
                'is_active' => true,
            ]);
        }

        $vendorGamarra = Vendor::create([
            'business_name' => 'Textil Gamarra Promociones SAC',
            'trade_name' => 'Textil Gamarra Pro',
            'ruc' => '20601234561',
            'slug' => 'textil-gamarra-pro',
            'city' => 'La Victoria, Lima',
            'phone' => '959199368',
            'commission_rate' => 12.00,
            'category_specialty' => 'Casacas Varsity, Poleras y Polos Pima',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $superAdmin?->id,
        ]);

        $vendorSouvenirs = Vendor::create([
            'business_name' => 'Souvenirs & Grabados Lima EIRL',
            'trade_name' => 'Grabados JMS Lima',
            'ruc' => '20559876543',
            'slug' => 'souvenirs-grabados-lima',
            'city' => 'Lima',
            'phone' => '959199368',
            'commission_rate' => 10.00,
            'category_specialty' => 'Joyería de Promoción, Medallas, Cristalería y Souvenirs',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $superAdmin?->id,
        ]);

        $cats = [
            'casacas' => Category::create(['name' => 'Casacas y Poleras', 'slug' => 'casacas-poleras', 'description' => 'Casacas varsity y poleras con capucha bordadas', 'sort_order' => 1]),
            'polos' => Category::create(['name' => 'Polos Promocionales', 'slug' => 'polos-promocionales', 'description' => 'Polos piqué y algodón Pima peruano', 'sort_order' => 2]),
            'gorros' => Category::create(['name' => 'Gorros y Accesorios', 'slug' => 'gorros-accesorios', 'description' => 'Gorros drill bordados y accesorios', 'sort_order' => 3]),
            'tazas' => Category::create(['name' => 'Tazas y Termos', 'slug' => 'tazas-termos', 'description' => 'Tazas cerámicas AAA y termos grabados', 'sort_order' => 4]),
            'llaveros' => Category::create(['name' => 'Llaveros Metálicos', 'slug' => 'llaveros-metalicos', 'description' => 'Llaveros metálicos con estuche', 'sort_order' => 5]),
            'recordatorios' => Category::create(['name' => 'Recordatorios y Souvenirs', 'slug' => 'recordatorios-souvenirs', 'description' => 'Placas de cristal, cajas souvenir y recuerdos', 'sort_order' => 6]),
            'papeleria' => Category::create(['name' => 'Papelería y Anuarios', 'slug' => 'papeleria-anuarios', 'description' => 'Anuarios tapa dura, libretas y agendas', 'sort_order' => 7]),
            'joyeria' => Category::create(['name' => 'Anillos, Cadenas y Medallas', 'slug' => 'anillos-cadenas-medallas', 'description' => 'Joyería conmemorativa de promoción: anillos, cadenas y medallas', 'sort_order' => 8]),
            'corporativos' => Category::create(['name' => 'Mochilas y Brindis', 'slug' => 'mochilas-brindis', 'description' => 'Mochilas institucionales y copas de cristal tallado', 'sort_order' => 9]),
        ];

        $catalogItems = [
            ['cat' => 'casacas', 'vendor' => $vendorGamarra, 'name' => 'Casaca Varsity Bordada Multi-Colegio', 'sku' => 'CAS-UNI', 'cost' => 78.00, 'price' => 145.00, 'member' => 129.00, 'img' => '/images/products/casaca.jpg', 'size' => 'M / L', 'mat' => 'Paño 380g + Cuero ecológico'],
            ['cat' => 'casacas', 'vendor' => $vendorGamarra, 'name' => 'Polera Hoodie Franela Reactiva Personalizable', 'sku' => 'POLERA-UNI', 'cost' => 62.00, 'price' => 115.00, 'member' => 98.00, 'img' => '/images/products/polera.jpg', 'size' => 'M / L', 'mat' => 'Franela Algodón 360g'],
            ['cat' => 'polos', 'vendor' => $vendorGamarra, 'name' => 'Polo Piqué Algodón Pima Bordado Multi-Colegio', 'sku' => 'POL-UNI', 'cost' => 31.00, 'price' => 64.00, 'member' => 54.00, 'img' => '/images/products/polo.jpg', 'size' => 'M', 'mat' => 'Algodón Pima Peruano'],
            ['cat' => 'gorros', 'vendor' => $vendorGamarra, 'name' => 'Gorro Drill Bordado Personalizable Multi-Colegio', 'sku' => 'GOR-UNI', 'cost' => 17.50, 'price' => 38.00, 'member' => 32.00, 'img' => '/images/products/gorro.jpg', 'size' => 'Estándar', 'mat' => 'Drill Peinado 100%'],
            ['cat' => 'joyeria', 'vendor' => $vendorSouvenirs, 'name' => 'Anillo de Promoción Baño Oro 18K / Plata 925', 'sku' => 'ANI-PRO', 'cost' => 95.00, 'price' => 189.00, 'member' => 159.00, 'img' => '/images/products/anillo.jpg', 'size' => 'Talla 8', 'mat' => 'Plata 925 + Baño Oro + Gema'],
            ['cat' => 'joyeria', 'vendor' => $vendorSouvenirs, 'name' => 'Cadena con Dije Escudo Institucional en Acero/Plata', 'sku' => 'CAD-ESC', 'cost' => 68.00, 'price' => 145.00, 'member' => 122.00, 'img' => '/images/products/cadena.jpg', 'size' => '50 cm', 'mat' => 'Plata 925 / Acero Quirúrgico'],
            ['cat' => 'joyeria', 'vendor' => $vendorSouvenirs, 'name' => 'Medalla Conmemorativa Dorada + Cinta y Estuche', 'sku' => 'MED-HON', 'cost' => 24.00, 'price' => 55.00, 'member' => 45.00, 'img' => '/images/products/medalla.jpg', 'size' => '6 cm diámetro', 'mat' => 'Bronce Baño Oro + Cinta Seda'],
            ['cat' => 'tazas', 'vendor' => $vendorSouvenirs, 'name' => 'Taza Cerámica Conmemorativa Personalizable 11oz', 'sku' => 'TAZ-UNI', 'cost' => 14.00, 'price' => 34.00, 'member' => 28.00, 'img' => '/images/products/taza.jpg', 'size' => '11 oz', 'mat' => 'Cerámica AAA'],
            ['cat' => 'tazas', 'vendor' => $vendorSouvenirs, 'name' => 'Termo Térmico Inoxidable + Pin Metálico de Solapa', 'sku' => 'TER-PIN', 'cost' => 34.00, 'price' => 68.00, 'member' => 58.00, 'img' => '/images/products/termo.jpg', 'size' => '500 ml + Pin', 'mat' => 'Acero Inox 304'],
            ['cat' => 'llaveros', 'vendor' => $vendorSouvenirs, 'name' => 'Llavero Metálico Escudo Alto Relieve + Estuche', 'sku' => 'LLA-REL', 'cost' => 11.50, 'price' => 28.00, 'member' => 22.00, 'img' => '/images/products/llavero.jpg', 'size' => '5.5 cm', 'mat' => 'Metal Zamac + Estuche'],
            ['cat' => 'recordatorios', 'vendor' => $vendorSouvenirs, 'name' => 'Recordatorio Placa de Cristal Biselado en Base Nogal', 'sku' => 'REC-CRI', 'cost' => 64.00, 'price' => 120.00, 'member' => 105.00, 'img' => '/images/products/recordatorio.jpg', 'size' => '18 × 14 cm', 'mat' => 'Cristal biselado + Nogal'],
            ['cat' => 'recordatorios', 'vendor' => $vendorSouvenirs, 'name' => 'Caja Souvenir Premium de Aniversario', 'sku' => 'SOU-BOX', 'cost' => 38.00, 'price' => 75.00, 'member' => 64.00, 'img' => '/images/products/souvenir.jpg', 'size' => '22 × 22 cm', 'mat' => 'Cartón rígido + Listón'],
            ['cat' => 'papeleria', 'vendor' => $vendorSouvenirs, 'name' => 'Anuario Tapa Dura Conmemorativo + Lapicero', 'sku' => 'ANU-UNI', 'cost' => 48.00, 'price' => 95.00, 'member' => 82.00, 'img' => '/images/products/anuario.jpg', 'size' => 'Tapa Dura A4', 'mat' => 'Couche 170g + Pan de oro'],
            ['cat' => 'papeleria', 'vendor' => $vendorSouvenirs, 'name' => 'Libreta Agenda Ejecutiva Tapa Cuero + Lapicero Grabado', 'sku' => 'LIB-EJE', 'cost' => 32.00, 'price' => 68.00, 'member' => 56.00, 'img' => '/images/products/libreta.jpg', 'size' => 'A5', 'mat' => 'Biocuero + Acero'],
            ['cat' => 'corporativos', 'vendor' => $vendorGamarra, 'name' => 'Mochila Ejecutiva Porta Laptop Bordada de Promoción', 'sku' => 'MOC-EJE', 'cost' => 68.00, 'price' => 135.00, 'member' => 115.00, 'img' => '/images/products/mochila.jpg', 'size' => '15.6"', 'mat' => 'Lona Balística Impermeable'],
            ['cat' => 'corporativos', 'vendor' => $vendorSouvenirs, 'name' => 'Copa de Cristal Tallada para Brindis de Promoción', 'sku' => 'COP-CRI', 'cost' => 42.00, 'price' => 88.00, 'member' => 74.00, 'img' => '/images/products/copa.jpg', 'size' => '220 ml', 'mat' => 'Cristal Fino + Estuche'],
        ];

        foreach ($catalogItems as $item) {
            $p = Product::create([
                'institution_id' => null,
                'vendor_id' => $item['vendor']->id,
                'category_id' => $cats[$item['cat']]->id,
                'name' => $item['name'],
                'slug' => Str::slug($item['name']),
                'sku_prefix' => $item['sku'],
                'short_description' => $item['name'].' personalizable con insignia y promoción.',
                'description' => $item['name'].' fabricado bajo altos estándares de calidad con personalización del escudo escolar y año de promoción.',
                'purchase_price' => $item['cost'],
                'base_price' => $item['price'],
                'member_price' => $item['member'],
                'is_customizable' => true,
                'show_verified_vendor' => true,
                'image_path' => $item['img'],
                'is_featured' => true,
                'is_active' => true,
            ]);

            ProductVariant::create([
                'product_id' => $p->id,
                'sku' => $item['sku'].'-STD',
                'size' => $item['size'],
                'color' => 'Institucional Personalizable',
                'color_hex' => '#1D70B8',
                'material' => $item['mat'],
                'stock' => 40,
                'low_stock_threshold' => 6,
            ]);
        }

        // Productos de ejemplo asignados a cada colegio
        foreach ($institutions as $idx => $inst) {
            for ($k = 0; $k < 3; $k++) {
                $tpl = $catalogItems[($idx + $k * 5) % count($catalogItems)];
                $name = "{$tpl['name']} — {$inst->short_name}";
                $slug = Str::slug("{$tpl['sku']}-{$inst->slug}");
                $sku = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $inst->short_name), 0, 3)).'-'.($idx + 1).$k;

                $prod = Product::create([
                    'institution_id' => $inst->id,
                    'vendor_id' => $tpl['vendor']->id,
                    'category_id' => $cats[$tpl['cat']]->id,
                    'name' => $name,
                    'slug' => $slug,
                    'sku_prefix' => $sku,
                    'short_description' => "Edición oficial {$inst->short_name} ({$inst->city}) personalizable.",
                    'description' => "Edición conmemorativa de {$inst->name} personalizada con insignia y promoción.",
                    'purchase_price' => $tpl['cost'],
                    'base_price' => $tpl['price'],
                    'member_price' => $tpl['member'],
                    'is_customizable' => true,
                    'show_verified_vendor' => true,
                    'image_path' => $tpl['img'],
                    'is_featured' => $idx < 4 && $k === 0,
                    'is_active' => true,
                ]);

                ProductVariant::create([
                    'product_id' => $prod->id,
                    'sku' => $sku.'-STD',
                    'size' => $tpl['size'],
                    'color' => "Color Oficial {$inst->short_name}",
                    'color_hex' => $inst->primary_color,
                    'material' => $tpl['mat'],
                    'stock' => 30,
                    'low_stock_threshold' => 5,
                ]);
            }
        }

        Coupon::create(['code' => 'PROMO10', 'description' => '10% de descuento en toda la tienda', 'type' => 'percent', 'value' => 10, 'min_order_amount' => 50, 'is_active' => true]);
        Coupon::create(['code' => 'EGRESADO20', 'description' => 'S/ 20.00 de descuento en compras desde S/ 120', 'type' => 'fixed', 'value' => 20, 'min_order_amount' => 120, 'is_active' => true]);
        Coupon::create(['code' => 'BODASPLATA', 'description' => '15% especial para promociones de aniversario', 'type' => 'percent', 'value' => 15, 'min_order_amount' => 80, 'is_active' => true]);

        ShippingZone::create(['name' => 'Recojo en Institución o Delegado', 'courier_name' => 'Entrega Directa Delegación', 'scope' => 'pickup', 'rate' => 0.00, 'estimated_days' => 'Coordinación con delegado', 'description' => 'Sin costo de envío. Retira en el punto asignado por tu delegación.']);
        ShippingZone::create(['name' => 'Lima Metropolitana y Callao', 'courier_name' => 'MiPromo Express', 'scope' => 'local', 'rate' => 12.00, 'estimated_days' => '24 a 48 horas', 'description' => 'Reparto a domicilio u oficina en Lima y Callao.']);
        ShippingZone::create(['name' => 'Envío Nacional Domicilio — Olva Courier', 'courier_name' => 'Olva Courier', 'scope' => 'national', 'rate' => 22.00, 'estimated_days' => '2 a 4 días hábiles', 'description' => 'Cobertura a Piura, Chiclayo, Trujillo y todo el Perú.']);
        ShippingZone::create(['name' => 'Envío Nacional Agencia — Shalom', 'courier_name' => 'Shalom Cargo', 'scope' => 'national', 'rate' => 16.00, 'estimated_days' => '2 a 3 días hábiles', 'description' => 'Recojo en agencia Shalom más cercana en todo el país.']);

        MessageTemplate::create(['name' => 'Bienvenida a Ex-alumno', 'slug' => 'bienvenida-egresado', 'channel' => 'email', 'subject' => '¡Bienvenido a MiPromo!', 'body' => 'Hola {nombre}, tu cuenta está lista para comprar con tarifa especial de ex-alumno.']);
        MessageTemplate::create(['name' => 'Confirmación de Pago y SUNAT', 'slug' => 'confirmacion-pago-sunat', 'channel' => 'web', 'subject' => 'Pago conciliado y comprobante emitido', 'body' => 'Tu pago ha sido validado por Grupo JMSPERU. Descarga tu comprobante SUNAT desde Mi Cuenta.']);

        PromotionalBanner::create([
            'title' => 'Anillos, Cadenas, Medallas y Casacas de Promoción 2026',
            'subtitle' => 'Compra online en 2 minutos con Yape, Plin o Tarjeta usando el cupón PROMO10.',
            'badge_text' => 'Catálogo Ampliado',
            'season' => 'aniversario',
            'cta_label' => 'Comprar Ahora',
            'cta_url' => '/#tienda-catalogo',
            'bg_color' => '#0F2D4A',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        BlogPost::create([
            'institution_id' => $institutions[0]->id,
            'author_id' => $superAdmin?->id,
            'title' => 'Cómo organizar las casacas, anillos y medallas de tu promoción online',
            'slug' => 'organizar-casacas-anillos-medallas-promocion',
            'category' => 'guia',
            'excerpt' => 'Guía rápida para comprar en línea con descuentos por volumen, proveedores verificados y comprobante SUNAT.',
            'body' => 'Regístrate en 15 segundos, selecciona tu colegio o producto personalizable y aprovecha descuentos automáticos desde 12 unidades.',
            'image_path' => '/images/products/anillo.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        SystemSetting::firstOrCreate(
            ['key' => 'apiperu_enabled'],
            ['group' => 'apiperu', 'value' => '1', 'is_encrypted' => false, 'description' => 'Activa o desactiva las consultas DNI a apiperu.dev.']
        );
        SystemSetting::firstOrCreate(
            ['key' => 'apiperu_endpoint'],
            ['group' => 'apiperu', 'value' => 'https://api.apiperu.dev/dni', 'is_encrypted' => false, 'description' => 'Endpoint oficial REST de apiperu.dev para consultas RENIEC.']
        );
        SystemSetting::firstOrCreate(
            ['key' => 'whatsapp_number'],
            ['group' => 'general', 'value' => '51959199368', 'is_encrypted' => false, 'description' => 'Número oficial de WhatsApp comercial Grupo JMSPERU.']
        );
    }
}
