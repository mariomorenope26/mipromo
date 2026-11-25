<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AlumniProfile;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\Campaign;
use App\Models\CampaignParticipant;
use App\Models\Category;
use App\Models\Classroom;
use App\Models\Community;
use App\Models\CommunityAlbum;
use App\Models\CommunityAlbumPhoto;
use App\Models\CommunityCollection;
use App\Models\CommunityCollectionContribution;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\CommunityMember;
use App\Models\CommunityPoll;
use App\Models\CommunityPollOption;
use App\Models\CommunityPost;
use App\Models\Complaint;
use App\Models\ContentReport;
use App\Models\Coupon;
use App\Models\Institution;
use App\Models\MessageTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PromotionalBanner;
use App\Models\ReturnRequest;
use App\Models\ShippingZone;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Vendor;
use App\Models\VendorSettlement;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // 0. Super Administrador
        $superAdmin = User::create([
            'name' => 'Julio Moreno (Grupo JMSPERU)',
            'email' => 'admin@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::SuperAdmin->value,
            'phone' => '959199368',
            'dni' => '42158963',
            'city' => 'Lima',
            'referral_code' => 'JMSPERU01',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        // 1. Instituciones Educativas (>10 años de vida institucional)
        $guadalupe = Institution::create([
            'name' => 'I.E. Emblemática Nuestra Señora de Guadalupe',
            'slug' => 'guadalupe-lima',
            'short_name' => 'Guadalupe',
            'type' => 'colegio',
            'modular_code' => '0312456',
            'city' => 'Lima',
            'region' => 'Lima Metropolitana',
            'address' => 'Av. Alfonso Ugarte 1227, Cercado de Lima',
            'website_url' => 'https://colegioguadalupe.edu.pe',
            'founded_year' => 1840,
            'motto' => 'Juventud, Estudio y Patria',
            'history' => 'Fundado el 14 de noviembre de 1840, el Primer Colegio Nacional Benemérito de la República reúne a miles de promociones guadalupanas en todo el Perú.',
            'anthem_excerpt' => '¡Salve oh noble plantel centenario, templo augusto de ciencia y virtud!',
            'primary_color' => '#B91C1C',
            'secondary_color' => '#EAB308',
            'logo_path' => '/images/insignias/guadalupe-lima.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $alfonsoUgarte = Institution::create([
            'name' => 'I.E. Emblemática Alfonso Ugarte',
            'slug' => 'alfonso-ugarte-lima',
            'short_name' => 'Alfonso Ugarte',
            'type' => 'colegio',
            'modular_code' => '0325104',
            'city' => 'Lima',
            'region' => 'Lima Metropolitana',
            'address' => 'Av. Paseo de la República 3530, San Isidro, Lima',
            'founded_year' => 1928,
            'motto' => 'Voluntad, Disciplina y Acción',
            'history' => 'Gran Unidad Escolar emblemática de San Isidro fundada en 1928, reconocida por su trayectoria académica, cívica y deportiva en Lima Metropolitana.',
            'anthem_excerpt' => '¡Alfonso Ugarte, glorioso nombre que en nuestra mente siempre vivirá!',
            'primary_color' => '#6B0F2B',
            'secondary_color' => '#FACC15',
            'logo_path' => '/images/insignias/alfonso-ugarte-lima.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $melitonCarvajal = Institution::create([
            'name' => 'I.E. Emblemática Melitón Carvajal',
            'slug' => 'meliton-carvajal-lima',
            'short_name' => 'Melitón Carvajal',
            'type' => 'colegio',
            'modular_code' => '0329812',
            'city' => 'Lima',
            'region' => 'Lima Metropolitana',
            'address' => 'Av. Prolongación Iquitos 2001, Lince, Lima',
            'website_url' => 'https://ieemelitoncarvajal.edu.pe',
            'founded_year' => 1948,
            'motto' => 'Claridad en la mente, energía en el espíritu y dignidad en la lucha',
            'history' => 'Institución emblemática de Lince fundada en 1948 en honor al héroe naval Vicealmirante Melitón Carvajal Ambulodegui.',
            'anthem_excerpt' => '¡Carvajalinos, con paso firme y corazón leal hacia el porvenir!',
            'primary_color' => '#DC2626',
            'secondary_color' => '#0F2D4A',
            'logo_path' => '/images/insignias/meliton-carvajal-lima.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $salesianoPiura = Institution::create([
            'name' => 'Colegio Salesiano Don Bosco de Piura',
            'slug' => 'salesiano-don-bosco-piura',
            'short_name' => 'Salesiano Piura',
            'type' => 'colegio',
            'modular_code' => '0356120',
            'city' => 'Piura',
            'region' => 'Piura',
            'address' => 'Calle Libertad 422, Piura',
            'website_url' => 'https://salesianospiura.edu.pe',
            'founded_year' => 1906,
            'motto' => 'Buenos cristianos y honrados ciudadanos',
            'history' => 'Fundado el 19 de febrero de 1906 en el corazón de Piura, formando generaciones bajo el sistema preventivo de San Juan Bosco.',
            'anthem_excerpt' => '¡Salve Don Bosco, padre y maestro de la juventud piurana!',
            'primary_color' => '#C81E1E',
            'secondary_color' => '#F59E0B',
            'logo_path' => '/images/insignias/salesiano-don-bosco-piura.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $sanIgnacioPiura = Institution::create([
            'name' => 'Colegio San Ignacio de Loyola de Piura',
            'slug' => 'san-ignacio-piura',
            'short_name' => 'San Ignacio Piura',
            'type' => 'colegio',
            'modular_code' => '0358401',
            'city' => 'Piura',
            'region' => 'Piura',
            'address' => 'Av. Ramón Mujica s/n, Urb. San Eduardo, Piura',
            'website_url' => 'https://www.csil.edu.pe',
            'founded_year' => 1958,
            'motto' => 'En todo amar y servir',
            'history' => 'Obra educativa de la Compañía de Jesús fundada en Piura en 1958, miembro de la red jesuita FLACSI con sólida comunidad de antiguos alumnos.',
            'anthem_excerpt' => '¡Hombres y mujeres para los demás, con el fuego ignaciano en el corazón!',
            'primary_color' => '#1E3A8A',
            'secondary_color' => '#B4892C',
            'logo_path' => '/images/insignias/san-ignacio-piura.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $santaMariaPiura = Institution::create([
            'name' => 'Colegio Santa María de Piura',
            'slug' => 'santa-maria-piura',
            'short_name' => 'Santa María Piura',
            'type' => 'colegio',
            'modular_code' => '0359102',
            'city' => 'Piura',
            'region' => 'Piura',
            'address' => 'Urb. El Chipe, Piura',
            'website_url' => 'https://santamariapiura.edu.pe',
            'founded_year' => 1962,
            'motto' => 'Ciencia, Virtud y Acción',
            'history' => 'Dirigido por las Hermanas Misioneras Dominicas del Rosario desde 1962 en Piura, formando promociones comprometidas con la verdad y el servicio.',
            'anthem_excerpt' => '¡Santa María, luz de ciencia y virtud en el norte peruano!',
            'primary_color' => '#1E296B',
            'secondary_color' => '#3B82F6',
            'logo_path' => '/images/insignias/santa-maria-piura.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $sanjose = Institution::create([
            'name' => 'Colegio Nacional de San José de Chiclayo',
            'slug' => 'san-jose-chiclayo',
            'short_name' => 'San José Chiclayo',
            'type' => 'colegio',
            'modular_code' => '0445781',
            'city' => 'Chiclayo',
            'region' => 'Lambayeque',
            'address' => 'Av. Elvira García y García 615, Chiclayo',
            'founded_year' => 1859,
            'motto' => 'Perseverancia y Fraternidad Lambayecana',
            'history' => 'Referente educativo centenario del norte peruano desde 1859, integrando promociones históricas sanjosefinas y delegados regionales.',
            'anthem_excerpt' => '¡San José, antorcha viva de la juventud norteña!',
            'primary_color' => '#7F1D1D',
            'secondary_color' => '#EAB308',
            'logo_path' => '/images/insignias/san-jose-chiclayo.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $manuelPardo = Institution::create([
            'name' => 'Colegio Manuel Pardo de Chiclayo',
            'slug' => 'manuel-pardo-chiclayo',
            'short_name' => 'Manuel Pardo',
            'type' => 'colegio',
            'modular_code' => '0446210',
            'city' => 'Chiclayo',
            'region' => 'Lambayeque',
            'address' => 'Av. Luis Gonzales 1415, Chiclayo',
            'website_url' => 'https://www.cmpardo.com',
            'founded_year' => 1917,
            'motto' => '¡Amar, Evangelizar, Saber!',
            'history' => 'Institución educativa vicentina con más de un siglo de trayectoria en Chiclayo desde 1917, reconocida por su excelencia académica y deportiva.',
            'anthem_excerpt' => '¡Colegio Manuel Pardo, blasón de fe y saber en Lambayeque!',
            'primary_color' => '#047857',
            'secondary_color' => '#F59E0B',
            'logo_path' => '/images/insignias/manuel-pardo-chiclayo.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $sanAgustinChiclayo = Institution::create([
            'name' => 'Colegio San Agustín de Chiclayo',
            'slug' => 'san-agustin-chiclayo',
            'short_name' => 'San Agustín Chiclayo',
            'type' => 'colegio',
            'modular_code' => '0448109',
            'city' => 'Chiclayo',
            'region' => 'Lambayeque',
            'address' => 'Carretera a Pimentel Km 4.5, Chiclayo',
            'website_url' => 'https://www.sanagustinchiclayo.edu.pe',
            'founded_year' => 1966,
            'motto' => 'Cor unum in Deum — Un solo corazón hacia Dios',
            'history' => 'Fundado el 25 de febrero de 1966 por la Orden de San Agustín en Chiclayo, formando generaciones con identidad agustina.',
            'anthem_excerpt' => '¡En busca de la verdad, un solo corazón y una sola alma!',
            'primary_color' => '#0F294A',
            'secondary_color' => '#DC2626',
            'logo_path' => '/images/insignias/san-agustin-chiclayo.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $santaAngelaChiclayo = Institution::create([
            'name' => 'Colegio Santa Ángela de Chiclayo',
            'slug' => 'santa-angela-chiclayo',
            'short_name' => 'Santa Ángela',
            'type' => 'colegio',
            'modular_code' => '0447800',
            'city' => 'Chiclayo',
            'region' => 'Lambayeque',
            'address' => 'Av. Miguel Grau 140, Santa Victoria, Chiclayo',
            'website_url' => 'https://colegiosantaangela.edu.pe',
            'founded_year' => 1962,
            'motto' => 'Serviam — Fe, Ciencia y Servicio',
            'history' => 'Fundado en 1962 bajo la espiritualidad ursulina, referente de liderazgo femenino y excelencia cooperativa en Lambayeque.',
            'primary_color' => '#1E3A8A',
            'secondary_color' => '#64748B',
            'logo_path' => '/images/insignias/santa-angela-chiclayo.png',
            'commission_rate' => 10.00,
            'is_featured' => false,
            'is_published' => false,
            'is_active' => true,
        ]);

        $santaRosaTrujillo = Institution::create([
            'name' => 'I.E. Emblemática Santa Rosa de Trujillo',
            'slug' => 'santa-rosa-trujillo',
            'short_name' => 'Santa Rosa Trujillo',
            'type' => 'colegio',
            'modular_code' => '0389120',
            'city' => 'Trujillo',
            'region' => 'La Libertad',
            'address' => 'Jr. San Martín 392, Centro Histórico, Trujillo',
            'website_url' => 'https://santarosatrujillo.edu.pe',
            'founded_year' => 1898,
            'motto' => 'Luz y Verdad',
            'history' => 'Fundado el 30 de septiembre de 1898 por las Hermanas Dominicas de la Inmaculada Concepción, pilar centenario de la educación trujillana.',
            'anthem_excerpt' => '¡Santa Rosa de Trujillo, antorcha eterna de luz y verdad!',
            'primary_color' => '#18181B',
            'secondary_color' => '#F59E0B',
            'logo_path' => '/images/insignias/santa-rosa-trujillo.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $claretianoTrujillo = Institution::create([
            'name' => 'Colegio Claretiano de Trujillo',
            'slug' => 'claretiano-trujillo',
            'short_name' => 'Claretiano Trujillo',
            'type' => 'colegio',
            'modular_code' => '0391405',
            'city' => 'Trujillo',
            'region' => 'La Libertad',
            'address' => 'Av. Húsares de Junín 1255, Trujillo',
            'website_url' => 'https://claretianotrujillo.edu.pe',
            'founded_year' => 1960,
            'motto' => 'Educar en la fe, ciencia y valores evangélicos',
            'history' => 'Fundado el 22 de febrero de 1960 por los Misioneros Claretianos, con más de seis décadas formando líderes en La Libertad.',
            'anthem_excerpt' => '¡Antorcha claretiana que ilumina a Trujillo y el Perú!',
            'primary_color' => '#0B5394',
            'secondary_color' => '#F59E0B',
            'logo_path' => '/images/insignias/claretiano-trujillo.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $sanJoseObreroTrujillo = Institution::create([
            'name' => 'Colegio San José Obrero Marianistas de Trujillo',
            'slug' => 'san-jose-obrero-trujillo',
            'short_name' => 'San José Obrero',
            'type' => 'colegio',
            'modular_code' => '0390811',
            'city' => 'Trujillo',
            'region' => 'La Libertad',
            'address' => 'Av. América Sur 2550, Trujillo',
            'website_url' => 'https://sjo.edu.pe',
            'founded_year' => 1957,
            'motto' => 'Familia, Fe y Servicio',
            'history' => 'Autorizado en 1957 bajo el carisma de la Compañía de María (Marianistas) en Trujillo, reconocido por su formación humanista y deportiva.',
            'primary_color' => '#0E5A75',
            'secondary_color' => '#9F2B48',
            'logo_path' => '/images/insignias/san-jose-obrero-trujillo.png',
            'commission_rate' => 10.00,
            'is_featured' => false,
            'is_published' => false,
            'is_active' => true,
        ]);

        $ciencias = Institution::create([
            'name' => 'Colegio Nacional de Ciencias del Cusco',
            'slug' => 'ciencias-cusco',
            'short_name' => 'Ciencias Cusco',
            'type' => 'colegio',
            'modular_code' => '0418920',
            'city' => 'Cusco',
            'region' => 'Cusco',
            'address' => 'Plaza San Francisco s/n, Centro Histórico del Cusco',
            'founded_year' => 1825,
            'motto' => 'Ciencia, Honor y Tradición Imperial',
            'history' => 'Institución bolivariana fundada en 1825 por Simón Bolívar, cuna de generaciones comprometidas con el desarrollo del sur andino.',
            'anthem_excerpt' => '¡Adelante ciencianos del alma, con la luz del saber inmortal!',
            'primary_color' => '#1E2460',
            'secondary_color' => '#94A3B8',
            'logo_path' => '/images/insignias/ciencias-cusco.png',
            'commission_rate' => 10.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $independencia = Institution::create([
            'name' => 'I.E. Independencia Americana de Arequipa',
            'slug' => 'independencia-arequipa',
            'short_name' => 'Independencia AQP',
            'type' => 'colegio',
            'modular_code' => '0394112',
            'city' => 'Arequipa',
            'region' => 'Arequipa',
            'address' => 'Av. Independencia 1457, Arequipa',
            'founded_year' => 1827,
            'motto' => 'Lealtad, Cultura y Progreso',
            'history' => 'Alfareros del carácter arequipeño desde 1827, con una activa asociación de ex-alumnos alfeñiques en Arequipa, Lima y el extranjero.',
            'anthem_excerpt' => '¡Independencia, glorioso estandarte del Misti inmortal!',
            'primary_color' => '#B91C1C',
            'secondary_color' => '#0284C7',
            'logo_path' => '/images/insignias/independencia-arequipa.png',
            'commission_rate' => 12.00,
            'is_featured' => true,
            'is_published' => true,
            'approved_by' => $superAdmin->id,
            'approved_at' => now(),
            'is_active' => true,
        ]);

        $allInstitutions = [
            $guadalupe, $alfonsoUgarte, $melitonCarvajal,
            $salesianoPiura, $sanIgnacioPiura, $santaMariaPiura,
            $sanjose, $manuelPardo, $sanAgustinChiclayo, $santaAngelaChiclayo,
            $santaRosaTrujillo, $claretianoTrujillo, $sanJoseObreroTrujillo,
            $ciencias, $independencia,
        ];

        // 2. Usuarios y Delegados
        $instAdmin = User::create([
            'name' => 'Carlos Mendoza Salazar',
            'email' => 'colegio@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::InstitutionAdmin->value,
            'institution_id' => $guadalupe->id,
            'phone' => '987654321',
            'dni' => '40123456',
            'city' => 'Lima',
            'referral_code' => 'GUAADMIN',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $promoDelegate = User::create([
            'name' => 'Lucía Paredes Chávez',
            'email' => 'delegado@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::PromotionDelegate->value,
            'institution_id' => $guadalupe->id,
            'phone' => '991223344',
            'dni' => '43891234',
            'city' => 'Lima',
            'referral_code' => 'PROMO06L',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $aulaDelegate = User::create([
            'name' => 'Marco Quispe Huamán',
            'email' => 'aula@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::ClassroomDelegate->value,
            'institution_id' => $ciencias->id,
            'phone' => '984112233',
            'dni' => '44556677',
            'city' => 'Cusco',
            'referral_code' => 'CIEN2016',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $delegatesSeed = [
            [$alfonsoUgarte, 'Martín Saldarriaga León', 'delegado.au@jmsperu.pe', 'Lima', true],
            [$melitonCarvajal, 'Patricia Cárdenas Ruiz', 'delegado.mc@jmsperu.pe', 'Lima', true],
            [$salesianoPiura, 'Jorge Seminario Carrasco', 'delegado.salesiano@jmsperu.pe', 'Piura', true],
            [$sanIgnacioPiura, 'Gonzalo Burneo Vegas', 'delegado.ignacio@jmsperu.pe', 'Piura', true],
            [$santaMariaPiura, 'Claudia Timaná Flores', 'delegado.santamaria@jmsperu.pe', 'Piura', true],
            [$sanjose, 'Ricardo Samamé Bravo', 'delegado.sanjose@jmsperu.pe', 'Chiclayo', true],
            [$manuelPardo, 'Ernesto Checa Delgado', 'delegado.manuelpardo@jmsperu.pe', 'Chiclayo', true],
            [$sanAgustinChiclayo, 'Paola Cabrejos Ortiz', 'delegado.sanagustin@jmsperu.pe', 'Chiclayo', true],
            [$santaRosaTrujillo, 'Mariana Ganoza Vega', 'delegado.santarosa@jmsperu.pe', 'Trujillo', true],
            [$claretianoTrujillo, 'Andrés Rebaza Orbegoso', 'delegado.claretiano@jmsperu.pe', 'Trujillo', true],
            [$sanJoseObreroTrujillo, 'Diego Alva Quiroz (Pendiente Alta)', 'delegado.sjo@jmsperu.pe', 'Trujillo', false],
            [$independencia, 'Eduardo Polar Bustamante', 'delegado.independencia@jmsperu.pe', 'Arequipa', true],
        ];

        foreach ($delegatesSeed as $idx => [$inst, $name, $email, $city, $active]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => UserRole::PromotionDelegate->value,
                'institution_id' => $inst->id,
                'phone' => '959199368',
                'city' => $city,
                'referral_code' => 'DEL'.str_pad((string) ($idx + 1), 5, '0', STR_PAD_LEFT),
                'is_active' => $active,
                'privacy_consent_at' => now(),
            ]);
        }

        $vendorUser = User::create([
            'name' => 'Taller Textil Gamarra Pro',
            'email' => 'vendedor@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Vendor->value,
            'phone' => '966778899',
            'dni' => '41223344',
            'city' => 'Lima',
            'referral_code' => 'GAMARRA1',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $vendorSouvenirsUser = User::create([
            'name' => 'Grabados & Joyería JMS Lima',
            'email' => 'vendedor.joyas@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Vendor->value,
            'phone' => '959199368',
            'dni' => '42119988',
            'city' => 'Lima',
            'referral_code' => 'JOYAS01',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $alumniUser = User::create([
            'name' => 'Andrea Valdivia Toribio',
            'email' => 'egresado@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Alumni->value,
            'institution_id' => $guadalupe->id,
            'phone' => '955443322',
            'dni' => '45678912',
            'city' => 'Lima',
            'referral_code' => 'ANDREA06',
            'referred_by_user_id' => $promoDelegate->id,
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $alumniPiuraUser = User::create([
            'name' => 'Carlos Eduardo Seminario Rentería',
            'email' => 'egresado.piura@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Alumni->value,
            'institution_id' => $salesianoPiura->id,
            'phone' => '969445511',
            'dni' => '44118822',
            'city' => 'Piura',
            'referral_code' => 'PIURA06A',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $alumniTrujilloUser = User::create([
            'name' => 'Valeria Cabanillas Rodríguez',
            'email' => 'egresado.trujillo@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Alumni->value,
            'institution_id' => $santaRosaTrujillo->id,
            'phone' => '949887711',
            'dni' => '47221199',
            'city' => 'Trujillo',
            'referral_code' => 'TRUJ11V',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        User::create([
            'name' => 'Sofía León (Soporte MiPromo)',
            'email' => 'moderador@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Moderator->value,
            'phone' => '959199368',
            'dni' => '43551122',
            'city' => 'Lima',
            'referral_code' => 'MODJMS01',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $buyerUser = User::create([
            'name' => 'Pedro Rojas Castillo',
            'email' => 'comprador@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Buyer->value,
            'phone' => '944332211',
            'dni' => '46789012',
            'city' => 'Trujillo',
            'referral_code' => 'PEDROR01',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        $buyerLimaUser = User::create([
            'name' => 'Daniela Vásquez Núñez',
            'email' => 'comprador2@jmsperu.pe',
            'password' => $password,
            'role' => UserRole::Buyer->value,
            'phone' => '988776611',
            'dni' => '48332211',
            'city' => 'Chiclayo',
            'referral_code' => 'DANIELA1',
            'is_active' => true,
            'privacy_consent_at' => now(),
        ]);

        // 3. Promociones y Aulas
        $promoG2006 = Promotion::create([
            'institution_id' => $guadalupe->id,
            'year' => 2006,
            'name' => 'Promoción 2006 — Camino al Bicentenario',
            'slug' => 'promo-2006',
            'motto' => '20 años de hermandad guadalupana',
            'delegate_user_id' => $promoDelegate->id,
        ]);

        $promoG2001 = Promotion::create([
            'institution_id' => $guadalupe->id,
            'year' => 2001,
            'name' => 'Promoción 2001 — Bodas de Plata',
            'slug' => 'promo-2001-bodas-plata',
            'motto' => '25 años dejando huella',
            'delegate_user_id' => $instAdmin->id,
        ]);

        $promoPiura2006 = Promotion::create([
            'institution_id' => $salesianoPiura->id,
            'year' => 2006,
            'name' => 'Promoción 2006 — Centenario Salesiano Piura',
            'slug' => 'promo-2006-salesiano-piura',
            'motto' => '100 años del colegio en Piura',
        ]);

        $promoTrujillo2011 = Promotion::create([
            'institution_id' => $santaRosaTrujillo->id,
            'year' => 2011,
            'name' => 'Promoción 2011 — Luz y Verdad Trujillo',
            'slug' => 'promo-2011-santarosa-trujillo',
            'motto' => 'Siempre unidas desde Trujillo',
        ]);

        $promoC2016 = Promotion::create([
            'institution_id' => $ciencias->id,
            'year' => 2016,
            'name' => 'Promoción 2016 — 10 Años Imperiales',
            'slug' => 'promo-2016-cusco',
            'motto' => 'Orgullo cienciano por siempre',
            'delegate_user_id' => $aulaDelegate->id,
        ]);

        $promoI2001 = Promotion::create([
            'institution_id' => $independencia->id,
            'year' => 2001,
            'name' => 'Promoción 2001 — Alfeñiques de Plata',
            'slug' => 'promo-2001-arequipa',
            'motto' => 'Fieles al Misti y a nuestra promoción',
        ]);

        $promoSJ2010 = Promotion::create([
            'institution_id' => $sanjose->id,
            'year' => 2010,
            'name' => 'Promoción 2010 — Unidos del Norte',
            'slug' => 'promo-2010-chiclayo',
            'motto' => 'Siempre adelante San José',
        ]);

        $aula5A = Classroom::create(['promotion_id' => $promoG2006->id, 'grade' => '5to Secundaria', 'section' => 'A', 'delegate_user_id' => $promoDelegate->id]);
        $aula5B = Classroom::create(['promotion_id' => $promoG2006->id, 'grade' => '5to Secundaria', 'section' => 'B', 'delegate_user_id' => $alumniUser->id]);
        $aulaC5A = Classroom::create(['promotion_id' => $promoC2016->id, 'grade' => '5to Secundaria', 'section' => 'A', 'delegate_user_id' => $aulaDelegate->id]);
        $aulaI5A = Classroom::create(['promotion_id' => $promoI2001->id, 'grade' => '5to Secundaria', 'section' => 'Única']);

        // 4. Comunidades Jerárquicas + Colectas + Álbumes Históricos
        $commGuadalupe = Community::create([
            'institution_id' => $guadalupe->id,
            'type' => 'institution',
            'name' => 'Asociación de Ex-Alumnos Guadalupe',
            'slug' => 'comunidad-guadalupe-lima',
            'description' => 'Espacio oficial de todas las promociones de Nuestra Señora de Guadalupe. Noticias, ceremonias y convenios institucionales.',
            'created_by' => $instAdmin->id,
        ]);

        $commPromo2006 = Community::create([
            'institution_id' => $guadalupe->id,
            'promotion_id' => $promoG2006->id,
            'type' => 'promotion',
            'name' => 'Promoción 2006 — Guadalupe (20 Años)',
            'slug' => 'guadalupe-promo-2006',
            'description' => 'Organización del reencuentro por los 20 años, votaciones de diseño de casaca y colecta conmemorativa.',
            'created_by' => $promoDelegate->id,
        ]);

        Community::create([
            'institution_id' => $guadalupe->id,
            'promotion_id' => $promoG2006->id,
            'classroom_id' => $aula5A->id,
            'type' => 'classroom',
            'name' => 'Aula 5to A — Promoción 2006',
            'slug' => 'guadalupe-2006-5a',
            'description' => 'Muro privado de coordinación rápida de la sección 5to A.',
            'created_by' => $promoDelegate->id,
        ]);

        Community::create([
            'institution_id' => $salesianoPiura->id,
            'promotion_id' => $promoPiura2006->id,
            'type' => 'promotion',
            'name' => 'Salesiano Don Bosco Piura — Promo 2006',
            'slug' => 'salesiano-piura-2006',
            'description' => 'Comunidad de egresados salesianos de Piura, coordinación de reencuentro norteño.',
            'created_by' => $superAdmin->id,
        ]);

        Community::create([
            'institution_id' => $santaRosaTrujillo->id,
            'promotion_id' => $promoTrujillo2011->id,
            'type' => 'promotion',
            'name' => 'Santa Rosa de Trujillo — Promoción 2011',
            'slug' => 'santa-rosa-trujillo-2011',
            'description' => 'Encuentro de ex-alumnas rosarinas de Trujillo y coordinación de kits conmemorativos.',
            'created_by' => $superAdmin->id,
        ]);

        $commCiencias2016 = Community::create([
            'institution_id' => $ciencias->id,
            'promotion_id' => $promoC2016->id,
            'type' => 'promotion',
            'name' => 'Ciencias Cusco — Promoción 2016',
            'slug' => 'ciencias-cusco-2016',
            'description' => 'Comunidad oficial de la Promoción 2016 del Glorioso Colegio Nacional de Ciencias.',
            'created_by' => $aulaDelegate->id,
        ]);

        Community::create([
            'institution_id' => $independencia->id,
            'promotion_id' => $promoI2001->id,
            'type' => 'promotion',
            'name' => 'Independencia Arequipa — Bodas de Plata 2001',
            'slug' => 'independencia-aqp-2001',
            'description' => 'Rumbo a los 25 años de egreso en la Ciudad Blanca.',
            'created_by' => $superAdmin->id,
        ]);

        Community::create([
            'type' => 'interest',
            'name' => 'Red Nacional de Emprendedores Egresados',
            'slug' => 'red-emprendedores-peru',
            'description' => 'Grupo de networking profesional y negocios entre egresados de Piura, Chiclayo, Trujillo, Lima y todo el Perú.',
            'created_by' => $superAdmin->id,
        ]);

        foreach ([$superAdmin, $instAdmin, $promoDelegate, $alumniUser] as $member) {
            CommunityMember::create([
                'community_id' => $commPromo2006->id,
                'user_id' => $member->id,
                'member_role' => $member->id === $promoDelegate->id ? 'moderator' : 'member',
                'joined_at' => now()->subDays(15),
            ]);
            CommunityMember::create([
                'community_id' => $commGuadalupe->id,
                'user_id' => $member->id,
                'member_role' => $member->id === $instAdmin->id ? 'moderator' : 'member',
                'joined_at' => now()->subDays(30),
            ]);
        }

        $post1 = CommunityPost::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $promoDelegate->id,
            'category' => 'anuncio',
            'title' => '¡Abrimos la campaña oficial de la Casaca Varsity 20 Años!',
            'body' => 'Compañeros de la Promo 2006: ya pueden separar su casaca oficial con escudo bordado y nombre en la espalda. Con 30 unidades cerramos producción en Gamarra y despachamos a Lima y provincias.',
            'is_pinned' => true,
            'likes_count' => 18,
        ]);

        CommunityComment::create(['post_id' => $post1->id, 'user_id' => $alumniUser->id, 'body' => '¡Excelente iniciativa Lucía! Ya separé talla M con mi apellido y el año 2006 por Yape.']);
        CommunityComment::create(['post_id' => $post1->id, 'user_id' => $instAdmin->id, 'body' => 'Felicitaciones a la delegación 2006. Recuerden que el 10% de la campaña apoya el equipamiento del laboratorio del colegio.']);

        CommunityPost::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $alumniUser->id,
            'category' => 'recuerdo',
            'title' => 'Desfile Escolar 2006 en Av. Brasil',
            'body' => 'Comparto el recuerdo cuando ganamos el gallardete de escolta. ¿Quién tiene fotos digitales del baile de graduación para armar el anuario?',
            'likes_count' => 12,
        ]);

        CommunityPost::create([
            'community_id' => $commCiencias2016->id,
            'user_id' => $aulaDelegate->id,
            'category' => 'anuncio',
            'title' => '¡Meta superada en el Polo Piqué Bicentenario!',
            'body' => 'Alcanzamos 42 unidades reservadas. Coordinaremos la entrega consolidada en la Plaza San Francisco y envíos por Shalom a Lima y Arequipa.',
            'is_pinned' => true,
            'likes_count' => 24,
        ]);

        $event1 = CommunityEvent::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $promoDelegate->id,
            'title' => 'Almuerzo de Reencuentro — 20 Años Promo 2006',
            'description' => 'Reunión confraternidad con entrega presencial de casacas, sesión de fotos y música en vivo.',
            'location' => 'Club Social Guadalupano, Jesús María, Lima',
            'starts_at' => now()->addDays(25)->setTime(13, 0),
            'ticket_price' => 65.00,
            'max_attendees' => 120,
        ]);
        CommunityEventRsvp::create(['event_id' => $event1->id, 'user_id' => $promoDelegate->id, 'status' => 'going']);
        CommunityEventRsvp::create(['event_id' => $event1->id, 'user_id' => $alumniUser->id, 'status' => 'going']);

        $poll1 = CommunityPoll::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $promoDelegate->id,
            'question' => '¿Qué detalle adicional incluimos en el Pack Aniversario?',
            'closes_at' => now()->addDays(10),
            'is_active' => true,
        ]);
        CommunityPollOption::create(['poll_id' => $poll1->id, 'label' => 'Pin metálico dorado + Llavero escudo', 'votes_count' => 19]);
        CommunityPollOption::create(['poll_id' => $poll1->id, 'label' => 'Gorro bordado con año 2006', 'votes_count' => 14]);
        CommunityPollOption::create(['poll_id' => $poll1->id, 'label' => 'Taza térmica conmemorativa', 'votes_count' => 9]);

        // Colectas y Álbumes Históricos (Módulo D)
        $colecta1 = CommunityCollection::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $promoDelegate->id,
            'title' => 'Colecta Solidaria: Beca Escolar & Placa 20 Años',
            'description' => 'Aporte voluntario de egresados 2006 para entregar la placa conmemorativa al colegio y financiar 2 becas de útiles.',
            'goal_amount' => 1500.00,
            'current_amount' => 920.00,
            'ends_at' => now()->addDays(18),
            'status' => 'active',
        ]);
        CommunityCollectionContribution::create(['collection_id' => $colecta1->id, 'user_id' => $promoDelegate->id, 'contributor_name' => 'Lucía Paredes Chávez', 'amount' => 120.00, 'reference_code' => 'YAPE-30121']);
        CommunityCollectionContribution::create(['collection_id' => $colecta1->id, 'user_id' => $alumniUser->id, 'contributor_name' => 'Andrea Valdivia Toribio', 'amount' => 100.00, 'reference_code' => 'PLIN-99102']);

        $album1 = CommunityAlbum::create([
            'community_id' => $commPromo2006->id,
            'user_id' => $alumniUser->id,
            'title' => 'Recuerdos de Promoción y Anuario 2006',
            'year_label' => '2006',
            'description' => 'Fotografías del desfile escolar, viaje de promoción y ceremonia de clausura.',
        ]);
        CommunityAlbumPhoto::create(['album_id' => $album1->id, 'user_id' => $alumniUser->id, 'caption' => 'Anuario impreso edición de gala 2006', 'image_path' => '/images/products/anuario.jpg']);
        CommunityAlbumPhoto::create(['album_id' => $album1->id, 'user_id' => $promoDelegate->id, 'caption' => 'Placa recordatoria de bodas de porcelana', 'image_path' => '/images/products/recordatorio.jpg']);

        // 5. Directorio de Ex-alumnos
        $alumniRows = [
            ['user_id' => $promoDelegate->id, 'inst' => $guadalupe->id, 'promo' => $promoG2006->id, 'aula' => $aula5A->id, 'name' => 'Lucía Paredes Chávez', 'dni' => '43891234', 'entry' => 2002, 'grad' => 2006, 'city' => 'Lima', 'phone' => '991223344', 'email' => 'delegado@jmsperu.pe', 'prof' => 'Ingeniera Industrial', 'comp' => 'Grupo Pacífico', 'status' => 'verified'],
            ['user_id' => $alumniUser->id, 'inst' => $guadalupe->id, 'promo' => $promoG2006->id, 'aula' => $aula5B->id, 'name' => 'Andrea Valdivia Toribio', 'dni' => '45678912', 'entry' => 2002, 'grad' => 2006, 'city' => 'Lima', 'phone' => '955443322', 'email' => 'egresado@jmsperu.pe', 'prof' => 'Arquitecta', 'comp' => 'Estudio Valdivia SAC', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $salesianoPiura->id, 'promo' => $promoPiura2006->id, 'aula' => null, 'name' => 'Jorge Seminario Carrasco', 'dni' => '43778899', 'entry' => 2002, 'grad' => 2006, 'city' => 'Piura', 'phone' => '969112233', 'email' => 'jseminario@example.pe', 'prof' => 'Ingeniero Agrónomo', 'comp' => 'AgroPiura Export', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $santaRosaTrujillo->id, 'promo' => $promoTrujillo2011->id, 'aula' => null, 'name' => 'Mariana Ganoza Vega', 'dni' => '46889900', 'entry' => 2007, 'grad' => 2011, 'city' => 'Trujillo', 'phone' => '949556677', 'email' => 'mganoza@example.pe', 'prof' => 'Médico Pediatra', 'comp' => 'Clínica San Pablo Trujillo', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $manuelPardo->id, 'promo' => null, 'aula' => null, 'name' => 'Ernesto Checa Delgado', 'dni' => '41225566', 'entry' => 1998, 'grad' => 2002, 'city' => 'Chiclayo', 'phone' => '979334455', 'email' => 'echeca@example.pe', 'prof' => 'Contador Auditor', 'comp' => 'Norte Consultores', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $guadalupe->id, 'promo' => $promoG2006->id, 'aula' => $aula5B->id, 'name' => 'Fernando Cáceres Medina', 'dni' => '43900112', 'entry' => 2002, 'grad' => 2006, 'city' => 'Arequipa', 'phone' => '959001122', 'email' => 'fcaceres@example.pe', 'prof' => 'Abogado Corporativo', 'comp' => 'Estudio Sur Legal', 'status' => 'pending'],
            ['user_id' => null, 'inst' => $guadalupe->id, 'promo' => $promoG2001->id, 'aula' => null, 'name' => 'Javier Soto Benavides', 'dni' => '40998877', 'entry' => 1997, 'grad' => 2001, 'city' => 'Lima', 'phone' => '998112233', 'email' => 'jsoto@example.pe', 'prof' => 'Contador Público', 'comp' => 'JMSPERU Solutions', 'status' => 'verified'],
            ['user_id' => $aulaDelegate->id, 'inst' => $ciencias->id, 'promo' => $promoC2016->id, 'aula' => $aulaC5A->id, 'name' => 'Marco Quispe Huamán', 'dni' => '44556677', 'entry' => 2012, 'grad' => 2016, 'city' => 'Cusco', 'phone' => '984112233', 'email' => 'aula@jmsperu.pe', 'prof' => 'Ingeniero de Software', 'comp' => 'Andes Tech', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $independencia->id, 'promo' => $promoI2001->id, 'aula' => $aulaI5A->id, 'name' => 'Eduardo Polar Bustamante', 'dni' => '41122339', 'entry' => 1997, 'grad' => 2001, 'city' => 'Arequipa', 'phone' => '958123456', 'email' => 'epolar@example.pe', 'prof' => 'Ingeniero Civil', 'comp' => 'Constructora Misti', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $sanjose->id, 'promo' => $promoSJ2010->id, 'aula' => null, 'name' => 'Diana Céspedes Mechán', 'dni' => '46123987', 'entry' => 2006, 'grad' => 2010, 'city' => 'Chiclayo', 'phone' => '979654321', 'email' => 'dcespedes@example.pe', 'prof' => 'Administradora de Empresas', 'comp' => 'AgroNorte SAC', 'status' => 'pending'],
            ['user_id' => $alumniPiuraUser->id, 'inst' => $salesianoPiura->id, 'promo' => $promoPiura2006->id, 'aula' => null, 'name' => 'Carlos Eduardo Seminario Rentería', 'dni' => '44118822', 'entry' => 2002, 'grad' => 2006, 'city' => 'Piura', 'phone' => '969445511', 'email' => 'egresado.piura@jmsperu.pe', 'prof' => 'Ingeniero Civil', 'comp' => 'Constructora Norte Piura', 'status' => 'verified'],
            ['user_id' => $alumniTrujilloUser->id, 'inst' => $santaRosaTrujillo->id, 'promo' => $promoTrujillo2011->id, 'aula' => null, 'name' => 'Valeria Cabanillas Rodríguez', 'dni' => '47221199', 'entry' => 2007, 'grad' => 2011, 'city' => 'Trujillo', 'phone' => '949887711', 'email' => 'egresado.trujillo@jmsperu.pe', 'prof' => 'Arquitecta Urbanista', 'comp' => 'Moche Diseño SAC', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $alfonsoUgarte->id, 'promo' => null, 'aula' => null, 'name' => 'Martín Saldarriaga León', 'dni' => '40881234', 'entry' => 1995, 'grad' => 1999, 'city' => 'Lima', 'phone' => '999441122', 'email' => 'msaldarriaga@example.pe', 'prof' => 'Economista', 'comp' => 'Finanzas del Pacífico', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $melitonCarvajal->id, 'promo' => null, 'aula' => null, 'name' => 'Patricia Cárdenas Ruiz', 'dni' => '41992233', 'entry' => 1999, 'grad' => 2003, 'city' => 'Lima', 'phone' => '987221144', 'email' => 'pcardenas@example.pe', 'prof' => 'Psicóloga Organizacional', 'comp' => 'Talento Perú', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $sanIgnacioPiura->id, 'promo' => null, 'aula' => null, 'name' => 'Gonzalo Burneo Vegas', 'dni' => '42883344', 'entry' => 2000, 'grad' => 2004, 'city' => 'Piura', 'phone' => '968112244', 'email' => 'gburneo@example.pe', 'prof' => 'Abogado Empresarial', 'comp' => 'Estudio Burneo Piura', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $santaMariaPiura->id, 'promo' => null, 'aula' => null, 'name' => 'Claudia Timaná Flores', 'dni' => '45994455', 'entry' => 2005, 'grad' => 2009, 'city' => 'Piura', 'phone' => '969554433', 'email' => 'ctimana@example.pe', 'prof' => 'Ingeniera Química', 'comp' => 'Refinería Norte', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $sanAgustinChiclayo->id, 'promo' => null, 'aula' => null, 'name' => 'Paola Cabrejos Ortiz', 'dni' => '46335566', 'entry' => 2008, 'grad' => 2012, 'city' => 'Chiclayo', 'phone' => '978223344', 'email' => 'pcabrejos@example.pe', 'prof' => 'Contadora Pública', 'comp' => 'Consultora Lambayeque', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $claretianoTrujillo->id, 'promo' => null, 'aula' => null, 'name' => 'Andrés Rebaza Orbegoso', 'dni' => '44776655', 'entry' => 2003, 'grad' => 2007, 'city' => 'Trujillo', 'phone' => '948223311', 'email' => 'arebaza@example.pe', 'prof' => 'Médico Cirujano', 'comp' => 'Hospital Regional Norte', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $guadalupe->id, 'promo' => $promoG2006->id, 'aula' => $aula5A->id, 'name' => 'Rodrigo Huerta Villalobos', 'dni' => '43771188', 'entry' => 2002, 'grad' => 2006, 'city' => 'Lima', 'phone' => '991558844', 'email' => 'rhuerta@example.pe', 'prof' => 'Administrador Financiero', 'comp' => 'Banca Corporativa', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $guadalupe->id, 'promo' => $promoG2006->id, 'aula' => $aula5B->id, 'name' => 'Claudia Morales Espinoza', 'dni' => '43882299', 'entry' => 2002, 'grad' => 2006, 'city' => 'Lima', 'phone' => '992669955', 'email' => 'cmorales@example.pe', 'prof' => 'Diseñadora UX/UI', 'comp' => 'Estudio Digital Lima', 'status' => 'pending'],
            ['user_id' => null, 'inst' => $salesianoPiura->id, 'promo' => $promoPiura2006->id, 'aula' => null, 'name' => 'Luis Alberto Chunga Zapata', 'dni' => '44229977', 'entry' => 2002, 'grad' => 2006, 'city' => 'Piura', 'phone' => '969118855', 'email' => 'lchunga@example.pe', 'prof' => 'Ingeniero Mecánico', 'comp' => 'Maquinarias Piura SAC', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $ciencias->id, 'promo' => $promoC2016->id, 'aula' => $aulaC5A->id, 'name' => 'Camila Condori Apaza', 'dni' => '70124578', 'entry' => 2012, 'grad' => 2016, 'city' => 'Cusco', 'phone' => '984776655', 'email' => 'ccondori@example.pe', 'prof' => 'Antropóloga Turística', 'comp' => 'Cusco Heritage Tours', 'status' => 'pending'],
            ['user_id' => null, 'inst' => $independencia->id, 'promo' => $promoI2001->id, 'aula' => $aulaI5A->id, 'name' => 'Renato Zúñiga Cornejo', 'dni' => '41336699', 'entry' => 1997, 'grad' => 2001, 'city' => 'Arequipa', 'phone' => '958334477', 'email' => 'rzuniga@example.pe', 'prof' => 'Ingeniero de Minas', 'comp' => 'Minera Sur Perú', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $sanjose->id, 'promo' => $promoSJ2010->id, 'aula' => null, 'name' => 'Oscar Mío Llontop', 'dni' => '46990011', 'entry' => 2006, 'grad' => 2010, 'city' => 'Chiclayo', 'phone' => '979112266', 'email' => 'omio@example.pe', 'prof' => 'Abogado Tributarista', 'comp' => 'Estudio Norte Legal', 'status' => 'verified'],
            ['user_id' => null, 'inst' => $claretianoTrujillo->id, 'promo' => null, 'aula' => null, 'name' => 'Sofía Gálvez Pretell', 'dni' => '47881122', 'entry' => 2009, 'grad' => 2013, 'city' => 'Trujillo', 'phone' => '949334488', 'email' => 'sgalvez@example.pe', 'prof' => 'Ingeniera Agroindustrial', 'comp' => 'Camposol Export', 'status' => 'verified'],
        ];

        foreach ($alumniRows as $row) {
            AlumniProfile::create([
                'user_id' => $row['user_id'],
                'institution_id' => $row['inst'],
                'promotion_id' => $row['promo'],
                'classroom_id' => $row['aula'],
                'full_name' => $row['name'],
                'dni' => $row['dni'],
                'entry_year' => $row['entry'],
                'graduation_year' => $row['grad'],
                'current_city' => $row['city'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'profession' => $row['prof'],
                'company' => $row['comp'],
                'verification_status' => $row['status'],
                'verified_by' => $row['status'] === 'verified' ? $instAdmin->id : null,
                'verified_at' => $row['status'] === 'verified' ? now()->subDays(10) : null,
                'privacy_settings' => ['show_phone' => true, 'show_email' => true, 'show_company' => true, 'show_city' => true],
                'import_batch' => 'SEED-INICIAL-2026',
            ]);
        }

        // 6. Proveedores (Área de Proveedores) & Categorías completas del Plan
        $vendorGamarra = Vendor::create([
            'user_id' => $vendorUser->id,
            'business_name' => 'Textil Gamarra Promociones SAC',
            'trade_name' => 'Textil Gamarra Pro',
            'ruc' => '20601234561',
            'slug' => 'textil-gamarra-pro',
            'city' => 'La Victoria, Lima',
            'address' => 'Jr. Sebastián Barranca 1420, Galería Moda Textil, La Victoria',
            'phone' => '966778899',
            'contact_name' => 'Renzo Paredes Quispe',
            'email' => 'produccion@textilgamarra.pe',
            'category_specialty' => 'Casacas Varsity, Poleras y Polos Pima Bordados',
            'payment_terms' => '50% adelanto / 50% contraentrega',
            'bank_name' => 'BCP',
            'bank_account' => '191-2248910-0-15',
            'bank_cci' => '00219100224891001554',
            'lead_time_days' => 8,
            'rating' => 4.95,
            'commission_rate' => 12.00,
            'description' => 'Confección especializada de casacas varsity, polos piqué bordados, gorros y poleras institucionales para promociones escolares.',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now()->subDays(15),
            'verified_by' => $superAdmin->id,
            'internal_notes' => 'Taller homologado con muestras aprobadas en paño 380g y algodón Pima.',
        ]);

        $vendorSouvenirs = Vendor::create([
            'user_id' => $vendorSouvenirsUser->id,
            'business_name' => 'Souvenirs & Grabados Lima EIRL',
            'trade_name' => 'Grabados JMS Lima',
            'ruc' => '20559876543',
            'slug' => 'souvenirs-grabados-lima',
            'city' => 'Lima',
            'address' => 'Av. Garcilaso de la Vega 1140, Cercado de Lima',
            'phone' => '959199368',
            'contact_name' => 'Julio Moreno',
            'email' => 'grabados@jmsperu.pe',
            'category_specialty' => 'Cristal Láser, Llaveros Zamac, Termos y Anuarios Tapa Dura',
            'payment_terms' => 'Contraentrega / Corte quincenal',
            'bank_name' => 'BBVA',
            'bank_account' => '0011-0190-0200458912',
            'bank_cci' => '01119000020045891201',
            'lead_time_days' => 5,
            'rating' => 5.00,
            'commission_rate' => 10.00,
            'description' => 'Recordatorios en cristal, tazas cerámicas, termos grabados láser, llaveros metálicos, cajas souvenir y anuarios de tapa dura.',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now()->subDays(20),
            'verified_by' => $superAdmin->id,
            'internal_notes' => 'Proveedor principal para placas de cristal y anuarios de Bodas de Plata.',
        ]);

        $vendorNorte = Vendor::create([
            'business_name' => 'Bordados del Norte Trujillano SRL',
            'trade_name' => 'Bordados Norte',
            'ruc' => '20488776655',
            'slug' => 'bordados-norte-trujillo',
            'city' => 'Trujillo',
            'address' => 'Jr. Pizarro 688, Centro de Trujillo',
            'phone' => '948112200',
            'contact_name' => 'Milagros Sánchez León',
            'email' => 'ventas@bordadosnorte.pe',
            'category_specialty' => 'Gorros Drill 3D y Bordados Regionales Norte',
            'payment_terms' => '50% adelanto / 50% envío',
            'bank_name' => 'Interbank',
            'bank_account' => '600-3004819201',
            'bank_cci' => '00360001300481920112',
            'lead_time_days' => 6,
            'rating' => 4.80,
            'commission_rate' => 11.00,
            'description' => 'Atención rápida para promociones de Piura, Chiclayo y Trujillo en gorros y polos bordados.',
            'is_active' => true,
            'is_verified' => true,
            'verified_at' => now()->subDays(7),
            'verified_by' => $superAdmin->id,
        ]);

        Vendor::create([
            'business_name' => 'Editorial Sur Andino EIRL',
            'trade_name' => 'Editorial Sur',
            'ruc' => '20609911223',
            'slug' => 'editorial-sur-andino',
            'city' => 'Arequipa',
            'address' => 'Calle Mercaderes 312, Arequipa',
            'phone' => '958774411',
            'contact_name' => 'Walter Huamán',
            'email' => 'contacto@editorialsur.pe',
            'category_specialty' => 'Impresión Offset y Anuarios Escolares',
            'payment_terms' => '30 días calendario',
            'lead_time_days' => 10,
            'rating' => 4.50,
            'commission_rate' => 10.00,
            'description' => 'Imprenta regional especializada en anuarios escolares e impresiones conmemorativas (en evaluación de muestras).',
            'is_active' => true,
            'is_verified' => false,
        ]);

        $catCasacas = Category::create(['name' => 'Casacas y Poleras', 'slug' => 'casacas-poleras', 'description' => 'Casacas varsity y poleras con capucha bordadas por promoción', 'sort_order' => 1]);
        $catPolos = Category::create(['name' => 'Polos Promocionales', 'slug' => 'polos-promocionales', 'description' => 'Polos piqué y algodón Pima peruano con escudo institucional', 'sort_order' => 2]);
        $catGorros = Category::create(['name' => 'Gorros y Accesorios', 'slug' => 'gorros-accesorios', 'description' => 'Gorros drill bordados y accesorios promocionales', 'sort_order' => 3]);
        $catTazas = Category::create(['name' => 'Tazas y Termos', 'slug' => 'tazas-termos', 'description' => 'Tazas cerámicas AAA y termos grabados láser con pin', 'sort_order' => 4]);
        $catLlaveros = Category::create(['name' => 'Llaveros Metálicos', 'slug' => 'llaveros-metalicos', 'description' => 'Llaveros metálicos en alto relieve con estuche de terciopelo', 'sort_order' => 5]);
        $catRecordatorios = Category::create(['name' => 'Recordatorios y Souvenirs', 'slug' => 'recordatorios-souvenirs', 'description' => 'Placas recordatorias en cristal, cajas regalo y pines conmemorativos', 'sort_order' => 6]);
        $catPapeleria = Category::create(['name' => 'Papelería y Anuarios', 'slug' => 'papeleria-anuarios', 'description' => 'Anuarios impresos en tapa dura, agendas y diplomas institucionales', 'sort_order' => 7]);
        $catJoyeria = Category::create(['name' => 'Anillos, Cadenas y Medallas', 'slug' => 'anillos-cadenas-medallas', 'description' => 'Anillos de promoción, cadenas con dije y medallas grabadas', 'sort_order' => 8]);
        $catCorporativos = Category::create(['name' => 'Mochilas y Brindis', 'slug' => 'mochilas-brindis', 'description' => 'Mochilas ejecutivas, copas de cristal tallado y sets de aniversario', 'sort_order' => 9]);

        // 7. Catálogo de Productos con FOTOS REALES, Precios de Compra/Venta y Proveedor Verificado
        $casacaGuadalupe = Product::create([
            'institution_id' => $guadalupe->id,
            'vendor_id' => $vendorGamarra->id,
            'category_id' => $catCasacas->id,
            'name' => 'Casaca Varsity Bordada — Edición Guadalupe',
            'slug' => 'casaca-varsity-guadalupe',
            'sku_prefix' => 'CAS-GUA',
            'short_description' => 'Paño importado con mangas cuero ecológico, escudo bordado en pecho y año de promoción.',
            'description' => 'Casaca universitaria clásica confeccionada en paño cardado peruano con mangas en contraste, broches metálicos, bordado computarizado del escudo institucional en el pecho y personalización de apellido y promoción en la espalda.',
            'purchase_price' => 78.00,
            'base_price' => 145.00,
            'member_price' => 129.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/casaca.jpg',
            'is_featured' => true,
        ]);
        foreach ([
            ['sku' => 'CAS-GUA-S-AZU', 'size' => 'S', 'color' => 'Rojo / Oro', 'hex' => '#B91C1C', 'stock' => 12],
            ['sku' => 'CAS-GUA-M-AZU', 'size' => 'M', 'color' => 'Rojo / Oro', 'hex' => '#B91C1C', 'stock' => 25],
            ['sku' => 'CAS-GUA-L-AZU', 'size' => 'L', 'color' => 'Rojo / Oro', 'hex' => '#B91C1C', 'stock' => 18],
            ['sku' => 'CAS-GUA-XL-AZU', 'size' => 'XL', 'color' => 'Rojo / Oro', 'hex' => '#B91C1C', 'stock' => 4],
        ] as $v) {
            ProductVariant::create(['product_id' => $casacaGuadalupe->id, 'sku' => $v['sku'], 'size' => $v['size'], 'color' => $v['color'], 'color_hex' => $v['hex'], 'material' => 'Paño 380g + Cuero ecológico', 'stock' => $v['stock'], 'low_stock_threshold' => 5]);
        }

        $poloSalesianoPiura = Product::create([
            'institution_id' => $salesianoPiura->id,
            'vendor_id' => $vendorGamarra->id,
            'category_id' => $catPolos->id,
            'name' => 'Polo Piqué Conmemorativo — Salesiano Piura',
            'slug' => 'polo-pique-salesiano-piura',
            'sku_prefix' => 'POL-SAL',
            'short_description' => 'Algodón Pima Piurano con insignia salesiana bordada y año de promoción.',
            'description' => 'Confeccionado en fino algodón pima piurano 24/1, ideal para el clima del norte peruano, con detalles en rojo y dorado salesiano.',
            'purchase_price' => 32.00,
            'base_price' => 65.00,
            'member_price' => 55.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/polo.jpg',
            'is_featured' => true,
        ]);
        foreach ([
            ['sku' => 'POL-SAL-M-ROJ', 'size' => 'M', 'color' => 'Rojo / Dorado', 'hex' => '#C81E1E', 'stock' => 30],
            ['sku' => 'POL-SAL-L-ROJ', 'size' => 'L', 'color' => 'Rojo / Dorado', 'hex' => '#C81E1E', 'stock' => 24],
        ] as $v) {
            ProductVariant::create(['product_id' => $poloSalesianoPiura->id, 'sku' => $v['sku'], 'size' => $v['size'], 'color' => $v['color'], 'color_hex' => $v['hex'], 'material' => 'Piqué Algodón Pima Piura', 'stock' => $v['stock'], 'low_stock_threshold' => 5]);
        }

        $poloCiencias = Product::create([
            'institution_id' => $ciencias->id,
            'vendor_id' => $vendorGamarra->id,
            'category_id' => $catPolos->id,
            'name' => 'Polo Piqué Oficial — Ciencias Cusco',
            'slug' => 'polo-pique-ciencias-cusco',
            'sku_prefix' => 'POL-CIE',
            'short_description' => 'Algodón Pima peruano 24/1 con cuello tejido bicolor y escudo bordado.',
            'description' => 'Polo camisero de piqué lacoste peruano de alta durabilidad, ideal para reencuentros y actividades deportivas de promoción.',
            'purchase_price' => 31.00,
            'base_price' => 62.00,
            'member_price' => 54.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/polo.jpg',
            'is_featured' => true,
        ]);
        foreach ([
            ['sku' => 'POL-CIE-S-AZU', 'size' => 'S', 'color' => 'Azul Real', 'hex' => '#1E2460', 'stock' => 30],
            ['sku' => 'POL-CIE-M-AZU', 'size' => 'M', 'color' => 'Azul Real', 'hex' => '#1E2460', 'stock' => 40],
            ['sku' => 'POL-CIE-L-AZU', 'size' => 'L', 'color' => 'Azul Real', 'hex' => '#1E2460', 'stock' => 22],
        ] as $v) {
            ProductVariant::create(['product_id' => $poloCiencias->id, 'sku' => $v['sku'], 'size' => $v['size'], 'color' => $v['color'], 'color_hex' => $v['hex'], 'material' => 'Piqué Algodón Pima', 'stock' => $v['stock'], 'low_stock_threshold' => 5]);
        }

        $gorroPromo = Product::create([
            'institution_id' => null,
            'vendor_id' => $vendorNorte->id,
            'category_id' => $catGorros->id,
            'name' => 'Gorro Drill Bordado Personalizable Multi-Colegio',
            'slug' => 'gorro-drill-bordado-personalizable',
            'sku_prefix' => 'GOR-UNI',
            'short_description' => 'Gorro 6 paños en drill americano con escudo y año de tu promoción.',
            'description' => 'Compatible con cualquier colegio o promoción registrada en MiPromo. Ajustador metálico posterior y bordado 3D frontal.',
            'purchase_price' => 17.50,
            'base_price' => 38.00,
            'member_price' => 32.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/gorro.jpg',
            'is_featured' => true,
        ]);
        ProductVariant::create(['product_id' => $gorroPromo->id, 'sku' => 'GOR-UNI-AZU', 'size' => 'Estándar', 'color' => 'Azul Institucional', 'color_hex' => '#1D70B8', 'material' => 'Drill peinado 100% algodón', 'stock' => 55, 'low_stock_threshold' => 8]);
        ProductVariant::create(['product_id' => $gorroPromo->id, 'sku' => 'GOR-UNI-NEG', 'size' => 'Estándar', 'color' => 'Negro Mate', 'color_hex' => '#1E293B', 'material' => 'Drill peinado 100% algodón', 'stock' => 38, 'low_stock_threshold' => 8]);

        $tazaSanJose = Product::create([
            'institution_id' => $sanjose->id,
            'vendor_id' => $vendorSouvenirs->id,
            'category_id' => $catTazas->id,
            'name' => 'Taza Cerámica Conmemorativa — San José Chiclayo',
            'slug' => 'taza-conmemorativa-san-jose',
            'sku_prefix' => 'TAZ-SJ',
            'short_description' => 'Taza AAA de 11 oz con escudo sanjosefino y caja individual de regalo.',
            'description' => 'Sublimación de alta definición con el escudo oficial guinda y dorado del Colegio Nacional de San José de Chiclayo.',
            'purchase_price' => 14.00,
            'base_price' => 34.00,
            'member_price' => 28.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/taza.jpg',
            'is_featured' => true,
        ]);
        ProductVariant::create(['product_id' => $tazaSanJose->id, 'sku' => 'TAZ-SJ-11OZ', 'size' => '11 oz', 'color' => 'Blanco / Guinda', 'color_hex' => '#7F1D1D', 'material' => 'Cerámica Premium AAA', 'stock' => 60, 'low_stock_threshold' => 10]);

        $anuarioIndep = Product::create([
            'institution_id' => $independencia->id,
            'vendor_id' => $vendorSouvenirs->id,
            'category_id' => $catPapeleria->id,
            'name' => 'Pack Bodas de Plata (Anuario Tapa Dura + Lapicero)',
            'slug' => 'pack-bodas-plata-independencia',
            'sku_prefix' => 'ANU-IND',
            'short_description' => 'Anuario impreso a todo color de 80 páginas con repujado dorado y lapicero metálico.',
            'description' => 'Edición de colección para celebrar los 25 años de promoción con biografías, fotos históricas del colegio y estuche de regalo.',
            'purchase_price' => 48.00,
            'base_price' => 95.00,
            'member_price' => 85.00,
            'is_customizable' => true,
            'is_preorder_only' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/anuario.jpg',
            'is_featured' => true,
        ]);
        ProductVariant::create(['product_id' => $anuarioIndep->id, 'sku' => 'ANU-IND-STD', 'size' => 'Tapa Dura A4', 'color' => 'Azul / Oro', 'color_hex' => '#B91C1C', 'material' => 'Couche 170g + Pan de oro', 'stock' => 3, 'low_stock_threshold' => 5]);

        $llaveroJms = Product::create([
            'institution_id' => null,
            'vendor_id' => $vendorSouvenirs->id,
            'category_id' => $catLlaveros->id,
            'name' => 'Llavero Metálico Escudo Alto Relieve + Estuche Terciopelo',
            'slug' => 'llavero-metalico-escudo-relieve',
            'sku_prefix' => 'LLA-REL',
            'short_description' => 'Aleación zamac niquelada con grabado de promoción en estuche azul de terciopelo.',
            'description' => 'Recuerdo elegante y duradero para cualquier promoción. Incluye estuche de presentación y grabado láser personalizado.',
            'purchase_price' => 11.50,
            'base_price' => 28.00,
            'member_price' => 22.00,
            'is_customizable' => true,
            'show_verified_vendor' => true,
            'image_path' => '/images/products/llavero.jpg',
            'is_featured' => true,
        ]);
        ProductVariant::create(['product_id' => $llaveroJms->id, 'sku' => 'LLA-REL-PLA', 'size' => '5.5 cm', 'color' => 'Plata / Estuche Azul', 'color_hex' => '#1D70B8', 'material' => 'Metal Zamac + Estuche', 'stock' => 110, 'low_stock_threshold' => 15]);

        // Productos adicionales Multi-Colegio cubriendo Poleras, Anillos, Cadenas, Medallas, Mochilas, Libretas, Copas, Recordatorios, Souvenirs y Termos del Plan
        $multiCatalog = [
            ['cat' => $catCasacas, 'vendor' => $vendorGamarra, 'name' => 'Polera Hoodie Franela Reactiva Personalizable', 'slug' => 'polera-hoodie-promocional', 'sku' => 'POLERA-UNI', 'cost' => 62.00, 'price' => 115.00, 'member' => 98.00, 'img' => '/images/products/polera.jpg', 'desc' => 'Polera con capucha en franela reactiva 360g con bordado de promoción e insignia del colegio.', 'size' => 'M / L', 'mat' => 'Franela Algodón 360g'],
            ['cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'name' => 'Anillo de Promoción Baño Oro 18K / Plata 925', 'slug' => 'anillo-promocion-oro-plata', 'sku' => 'ANI-PRO', 'cost' => 95.00, 'price' => 189.00, 'member' => 159.00, 'img' => '/images/products/anillo.jpg', 'desc' => 'Anillo de graduación en plata 925 con baño dorado 18K, gema institucional y grabado lateral de año y colegio.', 'size' => 'Talla 8', 'mat' => 'Plata 925 + Oro 18K'],
            ['cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'name' => 'Cadena con Dije Escudo Institucional en Plata 925', 'slug' => 'cadena-dije-escudo-plata', 'sku' => 'CAD-ESC', 'cost' => 68.00, 'price' => 145.00, 'member' => 122.00, 'img' => '/images/products/cadena.jpg', 'desc' => 'Cadena eslabonada fina en plata 925 con medalla o dije heráldico personalizado del colegio.', 'size' => '50 cm', 'mat' => 'Plata 925 Ley'],
            ['cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'name' => 'Medalla Conmemorativa Dorada + Cinta y Estuche', 'slug' => 'medalla-honor-dorada-cinta', 'sku' => 'MED-HON', 'cost' => 24.00, 'price' => 55.00, 'member' => 45.00, 'img' => '/images/products/medalla.jpg', 'desc' => 'Medalla en alto relieve bañada en oro con cinta institucional y estuche de presentación para ceremonias.', 'size' => '6 cm diámetro', 'mat' => 'Bronce Baño Oro'],
            ['cat' => $catCorporativos, 'vendor' => $vendorGamarra, 'name' => 'Mochila Ejecutiva Porta Laptop Bordada de Promoción', 'slug' => 'mochila-ejecutiva-promocion', 'sku' => 'MOC-EJE', 'cost' => 68.00, 'price' => 135.00, 'member' => 115.00, 'img' => '/images/products/mochila.jpg', 'desc' => 'Mochila porta laptop 15.6 pulgadas en lona resistente impermeable con escudo bordado del colegio.', 'size' => '15.6"', 'mat' => 'Lona Impermeable'],
            ['cat' => $catPapeleria, 'vendor' => $vendorSouvenirs, 'name' => 'Agenda / Libreta Ejecutiva Tapa Cuero + Lapicero', 'slug' => 'libreta-ejecutiva-lapicero', 'sku' => 'LIB-EJE', 'cost' => 32.00, 'price' => 68.00, 'member' => 56.00, 'img' => '/images/products/libreta.jpg', 'desc' => 'Set ejecutivo A5 en biocuero cosido con lapicero metálico grabado con insignia y año de promoción.', 'size' => 'A5 + Lapicero', 'mat' => 'Biocuero + Acero'],
            ['cat' => $catCorporativos, 'vendor' => $vendorSouvenirs, 'name' => 'Copa de Cristal Tallada para Brindis de Promoción', 'slug' => 'copa-cristal-brindis', 'sku' => 'COP-CRI', 'cost' => 42.00, 'price' => 88.00, 'member' => 74.00, 'img' => '/images/products/copa.jpg', 'desc' => 'Copa de cristal fino grabada con insignia y promoción, ideal para ceremonias de reencuentro y Bodas de Plata.', 'size' => '220 ml', 'mat' => 'Cristal Biselado'],
            ['cat' => $catRecordatorios, 'vendor' => $vendorSouvenirs, 'name' => 'Recordatorio Placa de Cristal Biselado en Base Nogal', 'slug' => 'recordatorio-placa-cristal-nogal', 'sku' => 'REC-CRI', 'cost' => 64.00, 'price' => 120.00, 'member' => 105.00, 'img' => '/images/products/recordatorio.jpg', 'desc' => 'Placa conmemorativa de cristal óptico con grabado láser del escudo, promoción y dedicatoria institucional.', 'size' => '18 × 14 cm', 'mat' => 'Cristal biselado + Nogal'],
            ['cat' => $catRecordatorios, 'vendor' => $vendorSouvenirs, 'name' => 'Caja Souvenir Premium de Aniversario con Cinta Satinada', 'slug' => 'caja-souvenir-aniversario', 'sku' => 'SOU-BOX', 'cost' => 38.00, 'price' => 75.00, 'member' => 64.00, 'img' => '/images/products/souvenir.jpg', 'desc' => 'Set de presentación listo para entregar en reencuentros, bodas de plata o ceremonias de promoción.', 'size' => 'Box 22 × 22 cm', 'mat' => 'Cartón rígido + Listón'],
            ['cat' => $catTazas, 'vendor' => $vendorSouvenirs, 'name' => 'Termo Térmico Inoxidable + Pin Metálico de Solapa', 'slug' => 'termo-inox-pin-solapa', 'sku' => 'TER-PIN', 'cost' => 34.00, 'price' => 68.00, 'member' => 58.00, 'img' => '/images/products/termo.jpg', 'desc' => 'Termo doble pared de acero inoxidable mate con grabado láser institucional y pin metálico de obsequio.', 'size' => '500 ml + Pin', 'mat' => 'Acero Inox 304'],
        ];

        foreach ($multiCatalog as $item) {
            $p = Product::create([
                'institution_id' => null,
                'vendor_id' => $item['vendor']->id,
                'category_id' => $item['cat']->id,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'sku_prefix' => $item['sku'],
                'short_description' => $item['desc'],
                'description' => $item['desc'],
                'purchase_price' => $item['cost'],
                'base_price' => $item['price'],
                'member_price' => $item['member'],
                'is_customizable' => true,
                'show_verified_vendor' => true,
                'image_path' => $item['img'],
                'is_featured' => true,
            ]);
            ProductVariant::create([
                'product_id' => $p->id,
                'sku' => $item['sku'].'-STD',
                'size' => $item['size'],
                'color' => 'Personalizado por Colegio',
                'color_hex' => '#1D70B8',
                'material' => $item['mat'],
                'stock' => 45,
                'low_stock_threshold' => 8,
            ]);
        }

        // Productos de ejemplo con FOTOS REALES asignados a CADA UNO de los 15 colegios
        $schoolPackTemplates = [
            ['suffix' => 'Casaca Varsity Oficial', 'cat' => $catCasacas, 'vendor' => $vendorGamarra, 'img' => '/images/products/casaca.jpg', 'cost' => 78.00, 'price' => 145.00, 'member' => 129.00, 'size' => 'M', 'mat' => 'Paño 380g + Cuero ecológico'],
            ['suffix' => 'Polera Hoodie Promoción', 'cat' => $catCasacas, 'vendor' => $vendorGamarra, 'img' => '/images/products/polera.jpg', 'cost' => 59.00, 'price' => 112.00, 'member' => 96.00, 'size' => 'L', 'mat' => 'Franela Reactiva 360g'],
            ['suffix' => 'Polo Piqué Institucional', 'cat' => $catPolos, 'vendor' => $vendorGamarra, 'img' => '/images/products/polo.jpg', 'cost' => 31.00, 'price' => 62.00, 'member' => 52.00, 'size' => 'M', 'mat' => 'Piqué Algodón Pima'],
            ['suffix' => 'Gorro Drill Bordado', 'cat' => $catGorros, 'vendor' => $vendorNorte, 'img' => '/images/products/gorro.jpg', 'cost' => 17.50, 'price' => 38.00, 'member' => 32.00, 'size' => 'Estándar', 'mat' => 'Drill Peinado 100%'],
            ['suffix' => 'Anillo Conmemorativo Promoción', 'cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/anillo.jpg', 'cost' => 95.00, 'price' => 185.00, 'member' => 155.00, 'size' => 'Talla 8', 'mat' => 'Plata 925 + Oro 18K'],
            ['suffix' => 'Cadena & Dije Escudo Plata 925', 'cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/cadena.jpg', 'cost' => 68.00, 'price' => 142.00, 'member' => 118.00, 'size' => '50 cm', 'mat' => 'Plata 925'],
            ['suffix' => 'Medalla Dorada de Honor', 'cat' => $catJoyeria, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/medalla.jpg', 'cost' => 24.00, 'price' => 54.00, 'member' => 44.00, 'size' => '6 cm', 'mat' => 'Bronce Baño Oro'],
            ['suffix' => 'Mochila Porta Laptop Bordada', 'cat' => $catCorporativos, 'vendor' => $vendorGamarra, 'img' => '/images/products/mochila.jpg', 'cost' => 68.00, 'price' => 132.00, 'member' => 112.00, 'size' => '15.6"', 'mat' => 'Lona Balística'],
            ['suffix' => 'Taza Cerámica Conmemorativa', 'cat' => $catTazas, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/taza.jpg', 'cost' => 14.00, 'price' => 34.00, 'member' => 28.00, 'size' => '11 oz', 'mat' => 'Cerámica AAA'],
            ['suffix' => 'Termo Inox + Pin Escudo', 'cat' => $catTazas, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/termo.jpg', 'cost' => 34.00, 'price' => 68.00, 'member' => 58.00, 'size' => '500 ml', 'mat' => 'Acero Inoxidable 304'],
            ['suffix' => 'Llavero Metálico + Estuche', 'cat' => $catLlaveros, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/llavero.jpg', 'cost' => 11.50, 'price' => 28.00, 'member' => 22.00, 'size' => '5.5 cm', 'mat' => 'Zamac Niquelado'],
            ['suffix' => 'Agenda Ejecutiva + Lapicero', 'cat' => $catPapeleria, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/libreta.jpg', 'cost' => 32.00, 'price' => 66.00, 'member' => 55.00, 'size' => 'A5', 'mat' => 'Biocuero + Metal'],
            ['suffix' => 'Copa Cristal Brindis Promoción', 'cat' => $catCorporativos, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/copa.jpg', 'cost' => 42.00, 'price' => 86.00, 'member' => 72.00, 'size' => '220 ml', 'mat' => 'Cristal Fino'],
            ['suffix' => 'Recordatorio Cristal & Nogal', 'cat' => $catRecordatorios, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/recordatorio.jpg', 'cost' => 63.00, 'price' => 118.00, 'member' => 99.00, 'size' => '18 × 14 cm', 'mat' => 'Cristal Óptico + Madera'],
            ['suffix' => 'Anuario Tapa Dura & Lapicero', 'cat' => $catPapeleria, 'vendor' => $vendorSouvenirs, 'img' => '/images/products/anuario.jpg', 'cost' => 47.00, 'price' => 92.00, 'member' => 79.00, 'size' => 'A4 Tapa Dura', 'mat' => 'Couche 170g + Pan de oro'],
        ];

        foreach ($allInstitutions as $idx => $inst) {
            // Cada colegio recibe 4 productos exclusivos con sus colores e insignia como datos de ejemplo
            for ($offset = 0; $offset < 4; $offset++) {
                $tpl = $schoolPackTemplates[($idx + $offset * 4) % count($schoolPackTemplates)];
                $prodName = "{$tpl['suffix']} — {$inst->short_name}";
                $slug = Str::slug("{$tpl['suffix']}-{$inst->slug}");
                if (Product::where('slug', $slug)->exists()) {
                    continue;
                }

                $skuPrefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $inst->short_name), 0, 3)).'-'.($idx + 1).$offset;
                $prod = Product::create([
                    'institution_id' => $inst->id,
                    'vendor_id' => $tpl['vendor']->id,
                    'category_id' => $tpl['cat']->id,
                    'name' => $prodName,
                    'slug' => $slug,
                    'sku_prefix' => $skuPrefix,
                    'short_description' => "Edición oficial de {$inst->short_name} ({$inst->city}) personalizada con insignia y promoción.",
                    'description' => "Producto oficial personalizado para ex-alumnos de {$inst->name} en {$inst->city}, confeccionado en los colores institucionales con bordado o grabado del escudo.",
                    'purchase_price' => $tpl['cost'],
                    'base_price' => $tpl['price'],
                    'member_price' => $tpl['member'],
                    'is_customizable' => true,
                    'show_verified_vendor' => true,
                    'image_path' => $tpl['img'],
                    'is_featured' => $idx < 4 && $offset === 0,
                ]);

                ProductVariant::create([
                    'product_id' => $prod->id,
                    'sku' => $skuPrefix.'-STD',
                    'size' => $tpl['size'],
                    'color' => "Color Oficial {$inst->short_name}",
                    'color_hex' => $inst->primary_color,
                    'material' => $tpl['mat'],
                    'stock' => 28,
                    'low_stock_threshold' => 5,
                ]);
            }
        }

        ProductReview::create([
            'product_id' => $casacaGuadalupe->id,
            'user_id' => $alumniUser->id,
            'rating' => 5,
            'comment' => 'Excelente calidad del paño y el bordado del escudo quedó impecable. Muy recomendada para el reencuentro.',
        ]);

        // 8. Campañas Grupales
        $camp1 = Campaign::create([
            'institution_id' => $guadalupe->id,
            'promotion_id' => $promoG2006->id,
            'product_id' => $casacaGuadalupe->id,
            'created_by' => $promoDelegate->id,
            'title' => 'Casaca Oficial Promoción 2006 — 20 Años Guadalupe',
            'slug' => 'casaca-promo-2006-guadalupe',
            'description' => 'Campaña grupal para confeccionar el lote conmemorativo por los 20 años. Separa tu casaca con 50% de adelanto o pago total.',
            'unit_price' => 125.00,
            'deposit_percent' => 50,
            'target_units' => 30,
            'current_units' => 24,
            'starts_at' => now()->subDays(12),
            'ends_at' => now()->addDays(14),
            'delivery_mode' => 'consolidated_delegate',
            'status' => 'active',
        ]);

        CampaignParticipant::create([
            'campaign_id' => $camp1->id,
            'user_id' => $promoDelegate->id,
            'variant_id' => $casacaGuadalupe->variants()->first()->id,
            'participant_name' => 'Lucía Paredes Chávez',
            'participant_phone' => '991223344',
            'quantity' => 1,
            'custom_name' => 'PAREDES',
            'custom_year' => '2006',
            'amount_paid' => 125.00,
            'total_amount' => 125.00,
            'payment_status' => 'paid',
        ]);

        CampaignParticipant::create([
            'campaign_id' => $camp1->id,
            'user_id' => $alumniUser->id,
            'variant_id' => $casacaGuadalupe->variants()->skip(1)->first()->id,
            'participant_name' => 'Andrea Valdivia Toribio',
            'participant_phone' => '955443322',
            'quantity' => 2,
            'custom_name' => 'VALDIVIA',
            'custom_year' => '2006',
            'amount_paid' => 125.00,
            'total_amount' => 250.00,
            'payment_status' => 'partial',
        ]);

        Campaign::create([
            'institution_id' => $salesianoPiura->id,
            'promotion_id' => $promoPiura2006->id,
            'product_id' => $poloSalesianoPiura->id,
            'created_by' => $superAdmin->id,
            'title' => 'Polo Pima 20 Años — Salesiano Don Bosco Piura',
            'slug' => 'polo-2006-salesiano-piura',
            'description' => 'Campaña norteña de polos conmemorativos bordados para el encuentro de ex-alumnos salesianos en Piura.',
            'unit_price' => 54.00,
            'deposit_percent' => 50,
            'target_units' => 35,
            'current_units' => 27,
            'starts_at' => now()->subDays(7),
            'ends_at' => now()->addDays(15),
            'delivery_mode' => 'consolidated_delegate',
            'status' => 'active',
        ]);

        Campaign::create([
            'institution_id' => $ciencias->id,
            'promotion_id' => $promoC2016->id,
            'product_id' => $poloCiencias->id,
            'created_by' => $aulaDelegate->id,
            'title' => 'Polo Piqué 10 Años — Promoción 2016 Ciencias Cusco',
            'slug' => 'polo-2016-ciencias-cusco',
            'description' => 'Campaña exitosa de polos conmemorativos bordados con insignia imperial. ¡Meta superada y en fase final de preventa!',
            'unit_price' => 52.00,
            'deposit_percent' => 100,
            'target_units' => 40,
            'current_units' => 42,
            'starts_at' => now()->subDays(20),
            'ends_at' => now()->addDays(6),
            'delivery_mode' => 'consolidated_delegate',
            'status' => 'goal_reached',
        ]);

        $anilloProd = Product::with('variants')->where('slug', 'anillo-promocion-oro-plata')->firstOrFail();

        $camp4 = Campaign::create([
            'institution_id' => $guadalupe->id,
            'promotion_id' => $promoG2001->id,
            'product_id' => $anilloProd->id,
            'created_by' => $superAdmin->id,
            'title' => 'Anillo Bodas de Plata Oro/Plata — Guadalupe 2001',
            'slug' => 'anillo-bodas-plata-guadalupe-2001',
            'description' => 'Edición conmemorativa por los 25 años de egreso con escudo tallado, año 2001 e iniciales grabadas por dentro.',
            'unit_price' => 159.00,
            'deposit_percent' => 50,
            'target_units' => 25,
            'current_units' => 19,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(18),
            'delivery_mode' => 'individual',
            'status' => 'active',
        ]);

        CampaignParticipant::create([
            'campaign_id' => $camp4->id,
            'user_id' => $superAdmin->id,
            'variant_id' => $anilloProd->variants->first()->id,
            'participant_name' => 'Javier Soto Benavides',
            'participant_phone' => '998112233',
            'quantity' => 1,
            'custom_name' => 'J. SOTO',
            'custom_year' => '2001',
            'amount_paid' => 159.00,
            'total_amount' => 159.00,
            'payment_status' => 'paid',
        ]);

        Campaign::create([
            'institution_id' => $sanjose->id,
            'promotion_id' => $promoSJ2010->id,
            'product_id' => $tazaSanJose->id,
            'created_by' => $superAdmin->id,
            'title' => 'Set Taza & Recuerdo 16 Años — San José Chiclayo 2010',
            'slug' => 'set-taza-san-jose-chiclayo-2010',
            'description' => 'Campaña completada para el encuentro anual sanjosefino en Chiclayo.',
            'unit_price' => 28.00,
            'deposit_percent' => 100,
            'target_units' => 50,
            'current_units' => 54,
            'starts_at' => now()->subDays(25),
            'ends_at' => now()->addDays(2),
            'delivery_mode' => 'consolidated_delegate',
            'status' => 'goal_reached',
        ]);

        // 9. Cupones y Zonas de Envío
        Coupon::create(['code' => 'PROMO10', 'description' => '10% de descuento en toda la tienda', 'type' => 'percent', 'value' => 10, 'min_order_amount' => 50, 'is_active' => true]);
        Coupon::create(['code' => 'EGRESADO20', 'description' => 'S/ 20.00 de descuento en compras desde S/ 120', 'type' => 'fixed', 'value' => 20, 'min_order_amount' => 120, 'is_active' => true]);
        Coupon::create(['code' => 'BODASPLATA', 'description' => '15% especial para promociones de aniversario', 'type' => 'percent', 'value' => 15, 'min_order_amount' => 80, 'is_active' => true]);

        ShippingZone::create(['name' => 'Recojo en Institución o Delegado', 'courier_name' => 'Entrega Directa Delegación', 'scope' => 'pickup', 'rate' => 0.00, 'estimated_days' => 'Coordinación con delegado', 'description' => 'Sin costo de envío. Retira en el punto asignado por tu institución o delegado.']);
        $zoneLima = ShippingZone::create(['name' => 'Lima Metropolitana y Callao', 'courier_name' => 'MiPromo Express', 'scope' => 'local', 'rate' => 12.00, 'estimated_days' => '24 a 48 horas', 'description' => 'Reparto a domicilio u oficina en todos los distritos de Lima y Callao.']);
        $zoneOlva = ShippingZone::create(['name' => 'Envío Nacional Domicilio — Olva Courier', 'courier_name' => 'Olva Courier', 'scope' => 'national', 'rate' => 22.00, 'estimated_days' => '2 a 4 días hábiles', 'description' => 'Cobertura a Piura, Chiclayo, Trujillo y todo el país con número de guía.']);
        ShippingZone::create(['name' => 'Envío Nacional Agencia — Shalom', 'courier_name' => 'Shalom Cargo', 'scope' => 'national', 'rate' => 16.00, 'estimated_days' => '2 a 3 días hábiles', 'description' => 'Recojo en agencia Shalom más cercana en todo el Perú.']);
        ShippingZone::create(['name' => 'Correo Certificado — Serpost', 'courier_name' => 'Serpost', 'scope' => 'national', 'rate' => 18.00, 'estimated_days' => '4 a 7 días hábiles', 'description' => 'Despacho certificado nacional vía Serpost.']);

        // 10. Pedidos de Ejemplo + Pagos + SUNAT + Liquidación Marketplace
        $order1 = Order::create([
            'order_number' => 'PED-2026-0001',
            'user_id' => $alumniUser->id,
            'institution_id' => $guadalupe->id,
            'customer_name' => 'Andrea Valdivia Toribio',
            'customer_email' => 'egresado@jmsperu.pe',
            'customer_phone' => '955443322',
            'customer_dni_ruc' => '45678912',
            'shipping_zone_id' => $zoneLima->id,
            'shipping_address' => 'Av. Salaverry 2410, Jesús María',
            'city' => 'Lima',
            'department' => 'Lima',
            'tracking_code' => 'MPR-882314PE',
            'courier_name' => 'MiPromo Express',
            'subtotal' => 151.00,
            'discount_amount' => 15.10,
            'shipping_cost' => 12.00,
            'total' => 147.90,
            'institution_commission_amount' => 14.79,
            'coupon_code' => 'PROMO10',
            'payment_status' => 'verified',
            'order_status' => 'shipped',
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $casacaGuadalupe->id,
            'variant_id' => $casacaGuadalupe->variants()->skip(1)->first()->id,
            'vendor_id' => $vendorGamarra->id,
            'product_name' => $casacaGuadalupe->name,
            'variant_label' => 'M / Rojo / Oro',
            'quantity' => 1,
            'unit_price' => 129.00,
            'unit_cost' => 78.00,
            'total_price' => 129.00,
            'customization_data' => ['escudo' => 'Guadalupe', 'promocion' => '2006', 'nombre_bordado' => 'A. VALDIVIA'],
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $llaveroJms->id,
            'variant_id' => $llaveroJms->variants()->first()->id,
            'vendor_id' => $vendorSouvenirs->id,
            'product_name' => $llaveroJms->name,
            'variant_label' => '5.5 cm / Plata / Estuche Azul',
            'quantity' => 1,
            'unit_price' => 22.00,
            'unit_cost' => 11.50,
            'total_price' => 22.00,
            'customization_data' => ['promocion' => '2006'],
        ]);

        Payment::create([
            'order_id' => $order1->id,
            'method' => 'yape',
            'amount' => 147.90,
            'reference_code' => 'YAPE-9045123',
            'status' => 'approved',
            'verified_by' => $superAdmin->id,
            'verified_at' => now()->subDays(2),
            'notes' => 'Yape verificado a cuenta JMSPERU 959199368.',
        ]);

        InvoiceService::issueForOrder($order1, 'boleta', 'Andrea Valdivia Toribio');

        $order2 = Order::create([
            'order_number' => 'PED-2026-0002',
            'user_id' => $buyerUser->id,
            'institution_id' => $ciencias->id,
            'customer_name' => 'Pedro Rojas Castillo',
            'customer_email' => 'comprador@jmsperu.pe',
            'customer_phone' => '944332211',
            'customer_dni_ruc' => '46789012',
            'shipping_zone_id' => $zoneOlva->id,
            'shipping_address' => 'Jr. Pizarro 415, Centro Histórico',
            'city' => 'Trujillo',
            'department' => 'La Libertad',
            'courier_name' => 'Olva Courier',
            'subtotal' => 94.00,
            'discount_amount' => 0.00,
            'shipping_cost' => 22.00,
            'total' => 116.00,
            'institution_commission_amount' => 11.60,
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $poloCiencias->id,
            'variant_id' => $poloCiencias->variants()->first()->id,
            'vendor_id' => $vendorGamarra->id,
            'product_name' => $poloCiencias->name,
            'variant_label' => 'S / Azul Real',
            'quantity' => 1,
            'unit_price' => 62.00,
            'unit_cost' => 31.00,
            'total_price' => 62.00,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $gorroPromo->id,
            'variant_id' => $gorroPromo->variants()->first()->id,
            'vendor_id' => $vendorNorte->id,
            'product_name' => $gorroPromo->name,
            'variant_label' => 'Estándar / Azul Institucional',
            'quantity' => 1,
            'unit_price' => 32.00,
            'unit_cost' => 17.50,
            'total_price' => 32.00,
        ]);

        Payment::create([
            'order_id' => $order2->id,
            'method' => 'plin',
            'amount' => 116.00,
            'reference_code' => 'PLIN-774120',
            'status' => 'pending_review',
            'notes' => 'Operación Plin pendiente de conciliación.',
        ]);

        $zonePickup = ShippingZone::where('scope', 'pickup')->firstOrFail();
        $zoneShalom = ShippingZone::where('courier_name', 'Shalom Cargo')->firstOrFail();
        $moderatorUser = User::where('email', 'moderador@jmsperu.pe')->firstOrFail();

        $catalogProducts = Product::with('variants')->whereIn('slug', [
            'polera-hoodie-promocional',
            'anillo-promocion-oro-plata',
            'cadena-dije-escudo-plata',
            'medalla-honor-dorada-cinta',
            'mochila-ejecutiva-promocion',
            'libreta-ejecutiva-lapicero',
            'copa-cristal-brindis',
            'recordatorio-placa-cristal-nogal',
            'caja-souvenir-aniversario',
            'termo-inox-pin-solapa',
        ])->get()->keyBy('slug');

        $extraOrders = [
            [
                'order_number' => 'PED-2026-0003',
                'user' => $alumniPiuraUser,
                'institution' => $salesianoPiura,
                'phone' => '969445511',
                'dni' => '44118822',
                'zone' => $zoneShalom,
                'address' => 'Av. Sánchez Cerro 840, Urb. Miraflores',
                'city' => 'Piura',
                'department' => 'Piura',
                'tracking_code' => 'SHM-441082PE',
                'discount' => 27.60,
                'coupon' => 'PROMO10',
                'payment_status' => 'verified',
                'order_status' => 'delivered',
                'days_ago' => 14,
                'payment_method' => 'bank_transfer',
                'payment_ref' => 'BCP-2026-99124',
                'payment_notes' => 'Transferencia BCP conciliada.',
                'invoice_type' => 'boleta',
                'items' => [
                    ['product' => $catalogProducts['anillo-promocion-oro-plata'], 'qty' => 1, 'unit_price' => 159.00, 'custom' => ['escudo' => 'Salesiano Piura', 'promocion' => '2006', 'iniciales' => 'C.S.']],
                    ['product' => $poloSalesianoPiura, 'qty' => 2, 'unit_price' => 58.50, 'custom' => ['promocion' => '2006', 'talla' => 'L']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0004',
                'user' => $alumniTrujilloUser,
                'institution' => $santaRosaTrujillo,
                'phone' => '949887711',
                'dni' => '47221199',
                'zone' => $zoneOlva,
                'address' => 'Calle San Martín 612, Centro Histórico',
                'city' => 'Trujillo',
                'department' => 'La Libertad',
                'tracking_code' => 'OLV-772310PE',
                'discount' => 20.00,
                'coupon' => 'EGRESADO20',
                'payment_status' => 'verified',
                'order_status' => 'delivered',
                'days_ago' => 11,
                'payment_method' => 'yape',
                'payment_ref' => 'YAPE-661209',
                'payment_notes' => 'Pago verificado vía Yape.',
                'invoice_type' => 'boleta',
                'items' => [
                    ['product' => $catalogProducts['cadena-dije-escudo-plata'], 'qty' => 1, 'unit_price' => 122.00, 'custom' => ['escudo' => 'Santa Rosa Trujillo', 'grabado' => '2011']],
                    ['product' => $catalogProducts['medalla-honor-dorada-cinta'], 'qty' => 2, 'unit_price' => 45.00, 'custom' => ['dedicatoria' => 'Bodas de Cristal 2011']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0005',
                'user' => $buyerLimaUser,
                'institution' => $melitonCarvajal,
                'phone' => '987114422',
                'dni' => '71234567',
                'zone' => $zoneLima,
                'address' => 'Av. Petit Thouars 3210, San Isidro',
                'city' => 'Lima',
                'department' => 'Lima',
                'tracking_code' => 'MPR-990145PE',
                'discount' => 0.00,
                'coupon' => null,
                'payment_status' => 'verified',
                'order_status' => 'in_production',
                'days_ago' => 6,
                'payment_method' => 'card_culqi',
                'payment_ref' => 'CARD-884192',
                'payment_notes' => 'Pago confirmado en pasarela.',
                'invoice_type' => 'factura',
                'legal_name' => 'Inversiones Carvajal SAC',
                'items' => [
                    ['product' => $catalogProducts['mochila-ejecutiva-promocion'], 'qty' => 2, 'unit_price' => 135.00, 'custom' => ['escudo' => 'Melitón Carvajal', 'anio' => '2014']],
                    ['product' => $catalogProducts['libreta-ejecutiva-lapicero'], 'qty' => 2, 'unit_price' => 68.00, 'custom' => ['promocion' => '2014']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0006',
                'user' => $promoDelegate,
                'institution' => $guadalupe,
                'phone' => '991223344',
                'dni' => '43891234',
                'zone' => $zonePickup,
                'address' => 'Av. Alfonso Ugarte 1227, Cercado de Lima',
                'city' => 'Lima',
                'department' => 'Lima',
                'tracking_code' => 'DEL-2026-06',
                'discount' => 45.00,
                'coupon' => 'BODASPLATA',
                'payment_status' => 'verified',
                'order_status' => 'confirmed',
                'days_ago' => 4,
                'payment_method' => 'bank_transfer',
                'payment_ref' => 'BBVA-441008',
                'payment_notes' => 'Consolidado delegación Promoción 2006.',
                'invoice_type' => 'boleta',
                'items' => [
                    ['product' => $catalogProducts['copa-cristal-brindis'], 'qty' => 4, 'unit_price' => 74.00, 'custom' => ['evento' => 'Reencuentro 20 Años Guadalupe']],
                    ['product' => $catalogProducts['recordatorio-placa-cristal-nogal'], 'qty' => 1, 'unit_price' => 105.00, 'custom' => ['homenaje' => 'Docentes Promoción 2006']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0007',
                'user' => $aulaDelegate,
                'institution' => $ciencias,
                'phone' => '984112233',
                'dni' => '44556677',
                'zone' => $zoneOlva,
                'address' => 'Av. El Sol 510, Cusco',
                'city' => 'Cusco',
                'department' => 'Cusco',
                'tracking_code' => 'OLV-881244PE',
                'discount' => 19.60,
                'coupon' => 'PROMO10',
                'payment_status' => 'verified',
                'order_status' => 'shipped',
                'days_ago' => 3,
                'payment_method' => 'plin',
                'payment_ref' => 'PLIN-339812',
                'payment_notes' => 'Plin verificado a número oficial.',
                'invoice_type' => 'boleta',
                'items' => [
                    ['product' => $catalogProducts['polera-hoodie-promocional'], 'qty' => 2, 'unit_price' => 98.00, 'custom' => ['escudo' => 'Ciencias Cusco', 'promocion' => '2016']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0008',
                'user' => $alumniUser,
                'institution' => $guadalupe,
                'phone' => '955443322',
                'dni' => '45678912',
                'zone' => $zoneLima,
                'address' => 'Av. Salaverry 2410, Jesús María',
                'city' => 'Lima',
                'department' => 'Lima',
                'tracking_code' => 'MPR-772110PE',
                'discount' => 0.00,
                'coupon' => null,
                'payment_status' => 'verified',
                'order_status' => 'delivered',
                'days_ago' => 19,
                'payment_method' => 'yape',
                'payment_ref' => 'YAPE-551904',
                'payment_notes' => 'Entrega completada (con devolución aprobada y NC).',
                'invoice_type' => 'boleta',
                'credit_note_reason' => 'Devolución aprobada por cambio de modelo conmemorativo',
                'items' => [
                    ['product' => $catalogProducts['caja-souvenir-aniversario'], 'qty' => 2, 'unit_price' => 64.00, 'custom' => ['evento' => 'Reencuentro 2006']],
                    ['product' => $catalogProducts['termo-inox-pin-solapa'], 'qty' => 1, 'unit_price' => 58.00, 'custom' => ['nombre' => 'A. VALDIVIA']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0009',
                'user' => $buyerUser,
                'institution' => $sanjose,
                'phone' => '944332211',
                'dni' => '46789012',
                'zone' => $zoneShalom,
                'address' => 'Av. Balta 920, Chiclayo',
                'city' => 'Chiclayo',
                'department' => 'Lambayeque',
                'tracking_code' => null,
                'discount' => 0.00,
                'coupon' => null,
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'days_ago' => 1,
                'payment_method' => 'yape',
                'payment_ref' => 'YAPE-PEND-909',
                'payment_notes' => 'Voucher enviado por validar.',
                'invoice_type' => null,
                'items' => [
                    ['product' => $tazaSanJose, 'qty' => 3, 'unit_price' => 34.00, 'custom' => ['promocion' => '2010']],
                ],
            ],
            [
                'order_number' => 'PED-2026-0010',
                'user' => $buyerLimaUser,
                'institution' => $independencia,
                'phone' => '987114422',
                'dni' => '71234567',
                'zone' => $zoneOlva,
                'address' => 'Av. Ejército 412, Cayma',
                'city' => 'Arequipa',
                'department' => 'Arequipa',
                'tracking_code' => null,
                'discount' => 0.00,
                'coupon' => null,
                'payment_status' => 'rejected',
                'order_status' => 'cancelled',
                'days_ago' => 8,
                'payment_method' => 'bank_transfer',
                'payment_ref' => 'ERR-40412',
                'payment_notes' => 'Referencia bancaria duplicada o incompleta.',
                'invoice_type' => null,
                'items' => [
                    ['product' => $anuarioIndep, 'qty' => 1, 'unit_price' => 92.00, 'custom' => ['promocion' => '2001']],
                ],
            ],
        ];

        $seededOrders = [];
        foreach ($extraOrders as $row) {
            $subtotal = collect($row['items'])->sum(fn (array $item) => round($item['qty'] * $item['unit_price'], 2));
            $shipping = (float) $row['zone']->rate;
            $total = max(0, round($subtotal - $row['discount'] + $shipping, 2));
            $order = Order::create([
                'order_number' => $row['order_number'],
                'user_id' => $row['user']->id,
                'institution_id' => $row['institution']->id,
                'customer_name' => $row['user']->name,
                'customer_email' => $row['user']->email,
                'customer_phone' => $row['phone'],
                'customer_dni_ruc' => $row['dni'],
                'shipping_zone_id' => $row['zone']->id,
                'shipping_address' => $row['address'],
                'city' => $row['city'],
                'department' => $row['department'],
                'tracking_code' => $row['tracking_code'],
                'courier_name' => $row['zone']->courier_name,
                'subtotal' => $subtotal,
                'discount_amount' => $row['discount'],
                'shipping_cost' => $shipping,
                'total' => $total,
                'institution_commission_amount' => round($total * 0.10, 2),
                'coupon_code' => $row['coupon'],
                'payment_status' => $row['payment_status'],
                'order_status' => $row['order_status'],
            ]);

            $timestamp = now()->subDays($row['days_ago']);
            $order->forceFill(['created_at' => $timestamp, 'updated_at' => $timestamp])->save();

            foreach ($row['items'] as $item) {
                $variant = $item['product']->variants->first() ?? $item['product']->variants()->first();
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'variant_id' => $variant?->id,
                    'vendor_id' => $item['product']->vendor_id,
                    'product_name' => $item['product']->name,
                    'variant_label' => $variant ? "{$variant->size} / {$variant->color}" : 'Estándar',
                    'quantity' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'unit_cost' => (float) $item['product']->purchase_price,
                    'total_price' => round($item['qty'] * $item['unit_price'], 2),
                    'customization_data' => $item['custom'],
                ]);
            }

            $payStatus = match ($row['payment_status']) {
                'verified' => 'approved',
                'rejected' => 'rejected',
                default => 'pending_review',
            };

            Payment::create([
                'order_id' => $order->id,
                'method' => $row['payment_method'],
                'amount' => $total,
                'reference_code' => $row['payment_ref'],
                'status' => $payStatus,
                'verified_by' => $payStatus === 'approved' ? $superAdmin->id : null,
                'verified_at' => $payStatus === 'approved' ? $timestamp->copy()->addHours(2) : null,
                'notes' => $row['payment_notes'],
            ]);

            if (! empty($row['invoice_type'])) {
                InvoiceService::issueForOrder($order, $row['invoice_type'], $row['legal_name'] ?? $row['user']->name);
            }
            if (! empty($row['credit_note_reason'])) {
                InvoiceService::issueCreditNote($order, $row['credit_note_reason']);
            }

            $seededOrders[$row['order_number']] = $order;
        }

        ReturnRequest::create([
            'order_id' => $seededOrders['PED-2026-0003']->id,
            'user_id' => $alumniPiuraUser->id,
            'request_type' => 'cambio_talla',
            'reason' => 'Solicito cambio de talla L a XL en uno de los polos Pima Salesiano Piura.',
            'status' => 'pending',
        ]);

        ReturnRequest::create([
            'order_id' => $seededOrders['PED-2026-0008']->id,
            'user_id' => $alumniUser->id,
            'request_type' => 'devolucion',
            'reason' => 'Reemplazo por set de aniversario en formato caja rígida premium.',
            'status' => 'approved',
            'admin_notes' => 'Nota de crédito SUNAT NC01 emitida y reembolso coordinado.',
            'resolved_by' => $superAdmin->id,
            'resolved_at' => now()->subDays(4),
        ]);

        VendorSettlement::create([
            'vendor_id' => $vendorGamarra->id,
            'period_label' => '2026-10',
            'gross_sales' => 451.00,
            'platform_commission' => 54.12,
            'net_payout' => 396.88,
            'status' => 'paid',
            'paid_at' => now()->subDays(5),
            'reference_code' => 'BCP-TRF-88412',
            'notes' => 'Liquidación quincenal de prendas entregadas.',
        ]);

        VendorSettlement::create([
            'vendor_id' => $vendorSouvenirs->id,
            'period_label' => '2026-10',
            'gross_sales' => 765.00,
            'platform_commission' => 114.75,
            'net_payout' => 650.25,
            'status' => 'pending',
            'notes' => 'Liquidación pendiente por anillos, cadenas, medallas y recordatorios entregados.',
        ]);

        VendorSettlement::create([
            'vendor_id' => $vendorNorte->id,
            'period_label' => '2026-10',
            'gross_sales' => 149.00,
            'platform_commission' => 14.90,
            'net_payout' => 134.10,
            'status' => 'pending',
            'notes' => 'Liquidación mensual de gorros y bordados norteños.',
        ]);

        ProductReview::create([
            'product_id' => $catalogProducts['anillo-promocion-oro-plata']->id,
            'user_id' => $alumniPiuraUser->id,
            'rating' => 5,
            'comment' => 'Acabado de joyería real con baño en oro 18K y grabado nítido del escudo Salesiano.',
        ]);

        ProductReview::create([
            'product_id' => $catalogProducts['cadena-dije-escudo-plata']->id,
            'user_id' => $alumniTrujilloUser->id,
            'rating' => 5,
            'comment' => 'Preciosa cadena en plata 925 y entrega puntual en Trujillo.',
        ]);

        ProductReview::create([
            'product_id' => $poloSalesianoPiura->id,
            'user_id' => $alumniPiuraUser->id,
            'rating' => 4,
            'comment' => 'Tela Pima muy fresca para el clima de Piura y buen ajuste de tallas.',
        ]);

        ProductReview::create([
            'product_id' => $catalogProducts['copa-cristal-brindis']->id,
            'user_id' => $promoDelegate->id,
            'rating' => 5,
            'comment' => 'Llegaron protegidas para el brindis de reencuentro de los 20 años.',
        ]);

        ContentReport::create([
            'user_id' => $alumniUser->id,
            'reportable_type' => CommunityPost::class,
            'reportable_id' => $post1->id,
            'reason' => 'Consulta de verificación de fecha del almuerzo',
            'details' => 'Reportado por participante para confirmar actualización de hora de recepción en el local.',
            'status' => 'open',
        ]);

        ContentReport::create([
            'user_id' => $buyerLimaUser->id,
            'reportable_type' => CommunityPost::class,
            'reportable_id' => $post1->id,
            'reason' => 'Solicitud de fijar post de tallas al inicio',
            'details' => 'Ya atendido y fijado por moderación.',
            'status' => 'resolved',
            'resolved_by' => $moderatorUser->id,
            'resolved_at' => now()->subDays(2),
        ]);

        // 11. Plantillas de Comunicación, Notificaciones, Banners de Temporada y Blog (Módulos K & L)
        MessageTemplate::create(['name' => 'Bienvenida a Ex-alumno', 'slug' => 'bienvenida-egresado', 'channel' => 'email', 'subject' => '¡Bienvenido a tu comunidad en MiPromo!', 'body' => 'Hola {nombre}, tu ficha de egresado en {colegio} ha sido verificada. Ya accedes a tarifas especiales de miembro.']);
        MessageTemplate::create(['name' => 'Recordatorio de Campaña por Cerrar', 'slug' => 'campana-por-cerrar', 'channel' => 'whatsapp', 'subject' => 'Últimos días para separar tu casaca', 'body' => '¡Hola! Faltan pocas unidades para cerrar la campaña de nuestra promoción. Separa con 50% en MiPromo.']);
        MessageTemplate::create(['name' => 'Confirmación de Pago y SUNAT', 'slug' => 'confirmacion-pago-sunat', 'channel' => 'web', 'subject' => 'Pago conciliado y comprobante emitido', 'body' => 'Tu pago ha sido validado por Grupo JMSPERU. Descarga tu boleta o factura electrónica desde Mi Cuenta.']);

        UserNotification::create([
            'user_id' => $alumniUser->id,
            'institution_id' => $guadalupe->id,
            'title' => 'Campaña Casaca 2006 al 80% de su meta',
            'body' => 'Faltan solo 6 unidades para iniciar la producción consolidada de la Promoción 2006.',
            'channel' => 'web',
            'action_url' => '/campanas/casaca-promo-2006-guadalupe',
        ]);

        UserNotification::create([
            'user_id' => $alumniPiuraUser->id,
            'institution_id' => $salesianoPiura->id,
            'title' => 'Pedido PED-2026-0003 entregado en Piura',
            'body' => 'Tu pedido de Anillo de Promoción y Polos Pima fue entregado vía Shalom Cargo.',
            'channel' => 'web',
            'action_url' => '/seguimiento?codigo=PED-2026-0003',
        ]);

        UserNotification::create([
            'user_id' => $promoDelegate->id,
            'institution_id' => $guadalupe->id,
            'title' => 'Nueva solicitud de cambio de talla pendiente',
            'body' => 'Revisa la solicitud abierta del pedido PED-2026-0001 en el panel de operaciones.',
            'channel' => 'web',
            'action_url' => '/admin/pedidos',
        ]);

        PromotionalBanner::create([
            'title' => 'Campaña Aniversarios & Bodas de Plata 2026',
            'subtitle' => 'Organiza las casacas, polos Pima y recordatorios de cristal para tu promoción en Piura, Chiclayo, Trujillo y Lima con 10% usando el cupón PROMO10.',
            'badge_text' => 'Temporada Aniversarios',
            'season' => 'aniversario',
            'cta_label' => 'Explorar Campañas Grupales',
            'cta_url' => '/campanas',
            'bg_color' => '#0F2D4A',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        PromotionalBanner::create([
            'title' => 'Invita a tu Promoción — Programa de Referidos',
            'subtitle' => 'Comparte tu enlace desde Mi Cuenta, reúne compañeros en tu comunidad y desbloquea descuentos grupales.',
            'badge_text' => 'Referidos MiPromo',
            'season' => 'reencuentro',
            'cta_label' => 'Obtener mi enlace',
            'cta_url' => '/mi-cuenta',
            'bg_color' => '#1D70B8',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        BlogPost::create([
            'institution_id' => $salesianoPiura->id,
            'author_id' => $superAdmin->id,
            'title' => 'Colegios históricos de Piura, Chiclayo y Trujillo se suman a MiPromo',
            'slug' => 'colegios-norte-peru-mipromo',
            'category' => 'noticia',
            'excerpt' => 'Instituciones emblemáticas del norte peruano con más de 60 años de historia ya cuentan con tienda oficial y comunidades por promoción.',
            'body' => "La red nacional de egresados MiPromo operada por Grupo JMSPERU amplía su cobertura en Piura, Chiclayo, Trujillo y Lima.\n\nCada colegio dado de alta por el Superadministrador cuenta con sus colores oficiales, insignia institucional y delegados acreditados para coordinar campañas grupales de casacas, polos Pima, gorros, tazas, llaveros y recordatorios.",
            'image_path' => '/images/products/casaca.jpg',
            'is_published' => true,
            'published_at' => now()->subDays(3),
        ]);

        BlogPost::create([
            'institution_id' => $guadalupe->id,
            'author_id' => $promoDelegate->id,
            'title' => 'Guía para Delegados: Cómo alcanzar la meta de tu campaña en 2 semanas',
            'slug' => 'guia-delegados-campana-exitosa',
            'category' => 'guia',
            'excerpt' => 'Consejos prácticos para abrir votaciones de diseño, activar reservas con 50% de adelanto y consolidar envíos a todo el país.',
            'body' => "1. Crea la encuesta de diseño en tu comunidad de promoción.\n2. Lanza la campaña grupal con adelanto del 50% para facilitar la reserva temprana.\n3. Consolida las tallas y nombres bordados en la orden de producción para tu taller o proveedor.\n4. Elige entrega presencial en el almuerzo de reencuentro o envíos por Olva / Shalom.",
            'image_path' => '/images/products/recordatorio.jpg',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        BlogPost::create([
            'institution_id' => $santaRosaTrujillo->id,
            'author_id' => $superAdmin->id,
            'title' => 'Pack Aniversario: Anuarios Tapa Dura y Recordatorios en Cristal',
            'slug' => 'pack-aniversario-anuarios-cristal',
            'category' => 'aniversario',
            'excerpt' => 'Conoce las nuevas opciones de papelería conmemorativa y placas grabadas en láser para Bodas de Plata y Oro.',
            'body' => 'Nuestros proveedores aliados ofrecen anuarios impresos en papel couche 170g con repujado dorado y placas de cristal biselado sobre base de nogal, ideales para homenajes a docentes y promociones.',
            'image_path' => '/images/products/anuario.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        Complaint::create([
            'complaint_code' => 'LRV-2026-0001',
            'order_number' => 'PED-2026-0001',
            'customer_name' => 'Andrea Valdivia Toribio',
            'customer_doc_number' => '45678912',
            'customer_email' => 'egresado@jmsperu.pe',
            'customer_phone' => '955443322',
            'type' => 'queja',
            'subject' => 'Consulta sobre horario de entrega en Jesús María',
            'detail' => 'Solicité coordinación en turno tarde para la entrega.',
            'request_detail' => 'Confirmar rango horario por WhatsApp antes del despacho.',
            'status' => 'resolved',
            'response' => 'Coordinado exitosamente con el motorizado de MiPromo Express.',
            'responded_at' => now()->subDay(),
        ]);

        Complaint::create([
            'complaint_code' => 'LRV-2026-0002',
            'order_number' => 'PED-2026-0008',
            'customer_name' => 'Andrea Valdivia Toribio',
            'customer_doc_number' => '45678912',
            'customer_email' => 'egresado@jmsperu.pe',
            'customer_phone' => '955443322',
            'type' => 'reclamo',
            'subject' => 'Solicitud de emisión de Nota de Crédito por cambio de souvenir',
            'detail' => 'Solicité reemplazo de caja souvenir por cambio de presentación.',
            'request_detail' => 'Emitir Nota de Crédito SUNAT vinculada al comprobante original.',
            'status' => 'resolved',
            'response' => 'Nota de Crédito NC01 emitida y reembolso aplicado correctamente.',
            'responded_at' => now()->subDays(3),
        ]);

        Complaint::create([
            'complaint_code' => 'LRV-2026-0003',
            'order_number' => 'PED-2026-0009',
            'customer_name' => 'Pedro Rojas Castillo',
            'customer_doc_number' => '46789012',
            'customer_email' => 'comprador@jmsperu.pe',
            'customer_phone' => '944332211',
            'type' => 'queja',
            'subject' => 'Demora en validación de comprobante Yape de provincia',
            'detail' => 'Realicé el depósito hace unas horas para despacho en Chiclayo.',
            'request_detail' => 'Confirmar conciliación para despacho prioritario en Shalom.',
            'status' => 'pending',
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

        AuditLog::create([
            'user_id' => $superAdmin->id,
            'action' => 'system.seeded',
            'description' => 'Sistema sembrado con 15 colegios históricos, catálogo completo con fotos reales, 10 pedidos, SUNAT, devoluciones y todos los módulos operativos.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
