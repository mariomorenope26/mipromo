<?php

namespace App\Services;

class UserManualService
{
    /**
     * Devuelve los capítulos estructurados del Manual de Uso Oficial MiPromo (Grupo JMSPERU),
     * compartidos entre la versión interactiva en línea y el PDF descargable.
     *
     * @return array<int, array<string, mixed>>
     */
    public function sections(): array
    {
        return [
            [
                'id' => '01-tienda-home',
                'number' => '01',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Portal Principal y Tienda Store-First',
                'route' => '/',
                'image' => 'images/manual/01-home-tienda.jpg',
                'summary' => 'La plataforma carga directamente en el escaparate comercial para acelerar la compra desde el primer segundo, mostrando el cupón vigente, el buscador inmediato y filtros por insignia institucional.',
                'steps' => [
                    'Utiliza la barra de búsqueda superior para encontrar prendas o souvenirs por nombre, colegio o ciudad (ejemplo: Guadalupe, Piura, anillo, casaca).',
                    'Haz clic en el selector rápido de insignias escolares para filtrar exclusivamente los productos oficiales de tu institución educativa.',
                    'Pulsa "Copiar PROMO10" en la cinta superior para guardar automáticamente el cupón de 10% de descuento antes de pasar por caja.',
                    'Si eres egresado o comprador nuevo, pulsa "Regístrate en 15s" para activar tarifas preferenciales sin llenar formularios extensos.',
                ],
                'recommendations' => [
                    'Si buscas comprar para todo un salón o promoción (12 o 30+ unidades), revisa primero los Packs Promocionales para maximizar el descuento por volumen.',
                    'Verifica siempre que el sello "Proveedor Verificado" figure en la tarjeta del producto para garantizar control de calidad y despacho oportuno.',
                ],
            ],
            [
                'id' => '02-como-comprar',
                'number' => '02',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Guía Comercial y Packs de Compra Rápida',
                'route' => '/como-funciona',
                'image' => 'images/manual/02-como-comprar.jpg',
                'summary' => 'Presentación persuasiva del flujo de compra en 3 pasos, combos con descuento directo y beneficios inmediatos de registro para ex-alumnos.',
                'steps' => [
                    'Revisa los 3 pasos del proceso: elige producto personalizado, selecciona variante/talla y paga con Yape, Plin, Tarjeta o Transferencia.',
                    'Elige uno de los Combos Promocionales ("Pack Reencuentro Total" o "Pack Ceremonia de Gala") y agrégalo al carrito en 1 clic.',
                    'Consulta el comparativo de ahorro entre precio público, tarifa ex-alumno y lotes mayoristas.',
                ],
                'recommendations' => [
                    'Recomienda esta vista a los delegados de salón o comités de promoción para explicar en minutos cómo comprar desde cualquier ciudad del Perú.',
                ],
            ],
            [
                'id' => '03-tienda-catalogo',
                'number' => '03',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Catálogo Completo, Anillos, Medallas y Souvenirs',
                'route' => '/tienda',
                'image' => 'images/manual/03-tienda-catalogo.jpg',
                'summary' => 'Tienda avanzada con filtros por categoría (Casacas, Polos Pima, Anillos/Cadenas/Medallas, Mochilas/Copas y Anuarios), colegio, orden de precio y packs en 1 clic.',
                'steps' => [
                    'Combina filtros laterales por categoría, colegio o marca multi-colegio para comparar precios públicos y tarifas de miembro.',
                    'Utiliza el filtro "Solo Proveedores Verificados" cuando requieras talleres certificados para pedidos institucionales.',
                    'Haz clic en "+ Agregar" para sumar artículos directamente o en el título del producto para personalizar talla, color o grabado.',
                ],
                'recommendations' => [
                    'En joyería conmemorativa (anillos, cadenas y medallas), entra a la ficha del producto para especificar la talla exacta y texto del grabado institucional.',
                ],
            ],
            [
                'id' => '04-producto-personalizador',
                'number' => '04',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Ficha de Producto, Descuento por Volumen y Personalizador',
                'route' => '/productos/{slug}',
                'image' => 'images/manual/04-producto-detalle.jpg',
                'summary' => 'Vista de detalle con tabla de descuentos automáticos por cantidad (-10% desde 12 uds y -18% desde 30 uds), variantes por SKU y campos de personalización.',
                'steps' => [
                    'Selecciona la variante (talla, color, material o medida) disponible y verifica el stock en tiempo real.',
                    'Ingresa los datos de personalización: nombre bordado o grabado, año de promoción y notas para taller.',
                    'Define la cantidad y pulsa "Agregar al Carrito" o "Comprar Ahora" para ir directo al pago.',
                ],
                'recommendations' => [
                    'Revisa dos veces la ortografía del nombre a bordar o grabar antes de confirmar el pedido, especialmente en lotes mayores a 12 unidades.',
                ],
            ],
            [
                'id' => '05-registro-dni',
                'number' => '05',
                'audience' => 'egresado',
                'audience_label' => 'Clientes y Ex-Alumnos',
                'title' => 'Registro en 15s y Autocompletado por DNI (apiperu.dev)',
                'route' => '/registro',
                'image' => 'images/manual/05-registro-dni-apiperu.jpg',
                'summary' => 'Creación rápida de cuenta para compradores o ex-alumnos consultando directamente el DNI peruano de 8 dígitos vía apiperu.dev (RENIEC).',
                'steps' => [
                    'Ingresa tu número de DNI de 8 dígitos y presiona "Buscar DNI" para autocompletar tus nombres y apellidos desde RENIEC vía apiperu.dev.',
                    'Completa tu correo electrónico real y define una contraseña segura de mínimo 8 caracteres combinando letras y números.',
                    'Opcionalmente, marca la casilla "¿Eres ex-alumno?" para seleccionar tu colegio y año de promoción y desbloquear la tarifa Ex-Alumno.',
                    'Acepta el consentimiento de la Ley N.° 29733 y pulsa "Crear Cuenta Gratis y Comprar".',
                ],
                'recommendations' => [
                    'Si el servicio externo RENIEC demorara o aún no tuviera token configurado, puedes escribir tu nombre completo manualmente y completar el registro sin interrupciones.',
                    'No reutilices contraseñas de otros sitios y actualízala periódicamente desde "Mi Cuenta → Seguridad".',
                ],
            ],
            [
                'id' => '06-carrito-cupones',
                'number' => '06',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Carrito Inteligente, Cupones y Venta Cruzada',
                'route' => '/carrito',
                'image' => 'images/manual/06-carrito-cupones.jpg',
                'summary' => 'Resumen editable de artículos, ahorro automático por volumen, aplicación de códigos promocionales y sugerencias complementarias.',
                'steps' => [
                    'Ajusta cantidades con el selector y observa la actualización inmediata del descuento por volumen cuando corresponda.',
                    'Aplica tu código promocional (PROMO10, EGRESADO20 o BODASPLATA) antes de avanzar a caja.',
                    'Agrega recuerdos complementarios desde la sección "Completa tu pedido" o pulsa "Ir a Pagar y Elegir Envío".',
                ],
                'recommendations' => [
                    'Verifica el monto total luego de aplicar cupones para asegurarte de cumplir con el mínimo requerido por campaña.',
                ],
            ],
            [
                'id' => '07-checkout-sunat',
                'number' => '07',
                'audience' => 'cliente',
                'audience_label' => 'Clientes y Visitantes',
                'title' => 'Checkout Express, Consulta DNI, SUNAT y Yape / Plin',
                'route' => '/checkout',
                'image' => 'images/manual/07-checkout-yape-sunat.jpg',
                'summary' => 'Proceso final de pago integrado con autocompletado de nombre por DNI (apiperu.dev), emisión de Boleta o Factura electrónica SUNAT, zonas Olva/Shalom y Yape/Plin QR.',
                'steps' => [
                    'Selecciona Boleta (DNI) o Factura (RUC). En modo Boleta, ingresa tu DNI y pulsa "Buscar DNI" para llenar automáticamente el titular del comprobante.',
                    'Si aún no tienes cuenta, deja marcada la opción "Crear mi cuenta MiPromo gratis" para asociar el pedido y descargar tu comprobante después.',
                    'Elige tu zona de envío (Lima o provincia vía Olva Courier / Shalom), selecciona Yape/Plin QR, Tarjeta o Transferencia y sube tu constancia o código de operación.',
                ],
                'recommendations' => [
                    'Adjunta captura legible o número de operación exacto al pagar con Yape/Plin (959199368) para que el equipo valide el pago en minutos.',
                    'Ante cualquier duda urgente de despacho, utiliza el botón directo de WhatsApp 959199368 indicando tu código PED-2026-XXXX.',
                ],
            ],
            [
                'id' => '08-directorio-dni',
                'number' => '08',
                'audience' => 'egresado',
                'audience_label' => 'Ex-Alumnos y Delegados',
                'title' => 'Directorio Nacional de Ex-Alumnos y Búsqueda por DNI',
                'route' => '/egresados',
                'image' => 'images/manual/08-directorio-egresados-dni.jpg',
                'summary' => 'Buscador público del padrón verificado por nombre, DNI, colegio, promoción o ciudad, con solicitud de alta asistida por apiperu.dev y detección de duplicados.',
                'steps' => [
                    'Usa el buscador superior indicando nombre, número de DNI, profesión, colegio o año de promoción para localizar egresados verificados.',
                    'Si aún no figuras en el padrón, baja al formulario "Solicitar Registro o Validación", escribe tu DNI y haz clic en "Buscar DNI".',
                    'El sistema valida duplicados automáticamente; si ya existes en el histórico importado por tu colegio, vincula la ficha a tu usuario.',
                ],
                'recommendations' => [
                    'Por cumplimiento de la Ley N.° 29733, el directorio público oculta los dígitos del DNI y permite a cada egresado elegir en "Mi Cuenta" qué datos mostrar.',
                ],
            ],
            [
                'id' => '09-comunidades-campanas',
                'number' => '09',
                'audience' => 'egresado',
                'audience_label' => 'Ex-Alumnos y Delegados',
                'title' => 'Comunidades por Promoción/Aula y Campañas Grupales',
                'route' => '/comunidades · /campanas',
                'image' => 'images/manual/09-comunidades-campanas.jpg',
                'summary' => 'Espacios organizados por institución, promoción y aula con muro social, encuestas, colectas de aniversario y campañas de producción grupal con meta mínima.',
                'steps' => [
                    'Únete a la comunidad oficial de tu colegio o promoción para publicar avisos, votar en encuestas y confirmar asistencia a eventos.',
                    'En Campañas Grupales, verifica la barra de progreso de participantes y únete antes de la fecha de cierre para asegurar la producción por lote.',
                ],
                'recommendations' => [
                    'Los delegados de promoción deben fijar una fecha de corte realista y compartir el enlace único de la campaña por WhatsApp para alcanzar la meta más rápido.',
                ],
            ],
            [
                'id' => '10-admin-dashboard',
                'number' => '10',
                'audience' => 'admin',
                'audience_label' => 'Delegados y Administración',
                'title' => 'Dashboard Gerencial Multi-Rol y Control Diario',
                'route' => '/admin',
                'image' => 'images/manual/10-admin-dashboard.jpg',
                'summary' => 'Centro de mando adaptado por rol (Superadmin, Admin Colegio, Delegado, Vendedor, Soporte) con KPIs de ventas, pagos pendientes de validar, egresados por aprobar y reclamos.',
                'steps' => [
                    'Inicia sesión desde /login con tu cuenta autorizada (sin usuarios de prueba en producción) y haz clic en "Panel Admin".',
                    'Revisa las alertas superiores: pedidos por verificar, pagos Yape/Plin pendientes y fichas nuevas en el padrón de egresados.',
                    'Accede desde la barra lateral al módulo operativo correspondiente según tu rol asignado.',
                ],
                'recommendations' => [
                    'Revisa diariamente "Pedidos, Pagos y SUNAT" y "Padrón e Importación CSV" para evitar demoras en confirmaciones de compra o beneficios de ex-alumnos.',
                ],
            ],
            [
                'id' => '11-admin-catalogo',
                'number' => '11',
                'audience' => 'admin',
                'audience_label' => 'Superadmin y Catálogo',
                'title' => 'CRUD de Catálogo, Precio Compra/Venta y Proveedor Verificado',
                'route' => '/admin/catalogo',
                'image' => 'images/manual/11-admin-catalogo-precios.jpg',
                'summary' => 'Gestión integral de productos, categorías y cupones: define precio de compra (costo), precio de venta, tarifa ex-alumno, proveedor asignado e imágenes en fondo blanco.',
                'steps' => [
                    'Completa el formulario "Registrar Nuevo Producto" seleccionando categoría, proveedor homologado, costo de compra, precio venta público y precio ex-alumno.',
                    'Activa o desactiva la casilla "Mostrar insignia Proveedor Verificado" que verá el comprador en la tienda.',
                    'En el listado inferior, administra variantes por SKU, edita precios/márgenes con el botón "Editar", actualiza stock o crea nuevas tallas/colores.',
                ],
                'recommendations' => [
                    'Mantén siempre actualizado el Precio de Compra (interno, oculto en APIs públicas) para que los Reportes Gerenciales calculen el margen neto real.',
                    'Utiliza fotografías sobre fondo blanco de 800×600 px para mantener la estética premium uniforme en toda la tienda.',
                ],
            ],
            [
                'id' => '12-admin-proveedores',
                'number' => '12',
                'audience' => 'superadmin',
                'audience_label' => 'Exclusivo Superadmin',
                'title' => 'Área de Proveedores Verificados y Liquidaciones',
                'route' => '/admin/proveedores',
                'image' => 'images/manual/12-admin-proveedores.jpg',
                'summary' => 'Registro homologado de talleres y proveedores (RUC, banco, CCI, comisión), control de verificación pública en tienda y generación de liquidaciones de pago.',
                'steps' => [
                    'Registra un proveedor con su razón social, RUC, especialidad, porcentaje de comisión y datos bancarios (ocultos para el público y API JSON).',
                    'Pulsa "Verificar / Homologar" para habilitar el sello de Proveedor Verificado en sus productos.',
                    'Genera liquidaciones por período y márcalas como pagadas al realizar el abono al taller.',
                ],
                'recommendations' => [
                    'Solo marca como Verificados a proveedores que hayan aprobado muestra física de costura, bordado o grabado.',
                ],
            ],
            [
                'id' => '13-admin-reportes',
                'number' => '13',
                'audience' => 'superadmin',
                'audience_label' => 'Exclusivo Superadmin',
                'title' => 'Suite de 10 Reportes Gerenciales y Exportación CSV',
                'route' => '/admin/reportes',
                'image' => 'images/manual/13-admin-reportes.jpg',
                'summary' => 'Diez reportes ejecutivos (Ventas por Colegio, Rentabilidad Compra vs Venta, Desempeño de Proveedores, Productos Top, Padrón Egresados, Pagos/Morosidad, Campañas, Cupones, Logística y Auditoría).',
                'steps' => [
                    'Filtra el rango de fechas o institución educativa en la parte superior del módulo de reportes.',
                    'Analiza los KPIs ejecutivos y revisa cada uno de los 10 tableros numerados del 01 al 10.',
                    'Descarga cualquier reporte en formato CSV compatible con Excel usando su botón "Exportar CSV".',
                ],
                'recommendations' => [
                    'Descarga mensualmente el Reporte 02 (Rentabilidad Real) y el Reporte 03 (Liquidación por Proveedor) para cierre contable y control de márgenes.',
                ],
            ],
            [
                'id' => '14-admin-colegios',
                'number' => '14',
                'audience' => 'superadmin',
                'audience_label' => 'Delegados y Superadmin',
                'title' => 'Colegios Históricos, Insignias PNG, Colores y Alta de Delegados',
                'route' => '/admin/instituciones',
                'image' => 'images/manual/14-admin-colegios-insignias.jpg',
                'summary' => 'Administración de colegios de Piura, Chiclayo, Trujillo, Lima y provincias con insignias PNG transparentes, colores institucionales y publicación condicionada al alta de al menos un delegado.',
                'steps' => [
                    'Registra el colegio (con más de 10 años de historia) subiendo su insignia PNG sin fondo y definiendo color primario y secundario.',
                    'Registra y da de alta al menos a un Delegado usando su DNI (consulta rápida apiperu.dev) o correo institucional.',
                    'Una vez activo el delegado, habilita el botón verde "Dar de Alta en Web Principal" para publicar el portal y perfil personalizado del colegio.',
                ],
                'recommendations' => [
                    'Sube insignias en formato PNG transparente para que se integren limpiamente tanto en tarjetas blancas como sobre cabeceras con color institucional.',
                ],
            ],
            [
                'id' => '15-admin-egresados-dni',
                'number' => '15',
                'audience' => 'admin',
                'audience_label' => 'Delegados y Administración',
                'title' => 'Padrón de Egresados, Alta Individual por DNI e Importación CSV',
                'route' => '/admin/egresados',
                'image' => 'images/manual/15-admin-egresados-dni.jpg',
                'summary' => 'Módulo de padrón con buscador en vivo por DNI/nombre/estado, registro individual consultando apiperu.dev, importación masiva CSV y flujo de validación.',
                'steps' => [
                    'Para registrar un egresado puntual, selecciona su colegio, digita el DNI de 8 dígitos y presiona "DNI" para autocompletar sus nombres desde apiperu.dev.',
                    'Usa el buscador del listado inferior para localizar ex-alumnos por DNI, correo o estado (Pendiente / Verificado / Rechazado).',
                    'Para cargas masivas de promociones completas, sube un archivo CSV con columnas: nombre_completo, dni, anio_egreso, ciudad, email, profesion.',
                ],
                'recommendations' => [
                    'Cuando valides un egresado cuyo DNI coincide con una cuenta comprador registrada, el sistema actualiza automáticamente su cuenta a tarifa Ex-Alumno.',
                ],
            ],
            [
                'id' => '16-admin-usuarios-dni',
                'number' => '16',
                'audience' => 'superadmin',
                'audience_label' => 'Exclusivo Superadmin',
                'title' => 'Gestión de Usuarios, 8 Roles, Seguridad y Registro por DNI',
                'route' => '/admin/usuarios',
                'image' => 'images/manual/16-admin-usuarios-dni.jpg',
                'summary' => 'Control centralizado de las cuentas del sistema en 8 niveles de rol, creación rápida de clientes o delegados por DNI, filtrado por DNI/correo y suspensión inmediata.',
                'steps' => [
                    'En "Crear Cliente / Ex-Alumno / Usuario por DNI", ingresa el DNI y pulsa "Consultar DNI" para prellenar nombres desde RENIEC.',
                    'Asigna el rol correcto y una contraseña segura de al menos 8 caracteres (letras y números).',
                    'Para revocar accesos sin perder historial de pedidos, desmarca "Activo" y pulsa "Guardar": la sesión del usuario se cerrará en su siguiente acción.',
                ],
                'recommendations' => [
                    'Aplica el principio de menor privilegio: otorga el rol Superadministrador únicamente a gerencia y TI autorizados.',
                ],
            ],
            [
                'id' => '17-admin-configuracion',
                'number' => '17',
                'audience' => 'superadmin',
                'audience_label' => 'Exclusivo Superadmin',
                'title' => 'Configuración, Token apiperu.dev, Backup, Blanqueo y Demo',
                'route' => '/admin/configuracion',
                'image' => 'images/manual/17-admin-configuracion-token.jpg',
                'summary' => 'Panel exclusivo para ingresar y probar en vivo el Token Bearer de apiperu.dev, configurar el WhatsApp oficial, descargar copias de seguridad ZIP/SQL, iniciar datos de ejemplo o blanquear el sistema.',
                'steps' => [
                    'Ingresa tu Token Bearer de apiperu.dev, marca la casilla de activación, guarda y usa "Probar Conexión" con un DNI de 8 dígitos.',
                    'En "Copia de Seguridad (Backup)", genera un archivo ZIP/SQL completo de todas las tablas de la base de datos antes de cualquier cambio mayor.',
                    'Usa "Iniciar Datos de Ejemplo" para poblar el catálogo, colegios y operaciones de prueba, o "Blanquear Sistema" (escribiendo BLANQUEAR + contraseña) para dejar limpio el entorno manteniendo solo al Superadministrador.',
                ],
                'recommendations' => [
                    'El token se cifra con Laravel Crypt AES-256 en la base de datos y nunca vuelve a mostrarse completo en HTML tras guardarlo.',
                    'El Manual de Uso interactivo (/manual) y su versión PDF (/manual/pdf) están protegidos dentro de la sesión autenticada de MiPromo.',
                    'Después de instalar en BanaHosting con /install.php, elimina public/install.php y confirma que storage/installed.lock esté presente.',
                ],
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, body: string}>
     */
    public function bestPractices(): array
    {
        return [
            [
                'title' => 'Seguridad en Hosting de Producción (BanaHosting)',
                'body' => 'Verifica APP_ENV=production, APP_DEBUG=false, SSL HTTPS activo y elimina public/install.php inmediatamente después de completar la instalación. Asegúrate de no tener cuentas demo ni contraseñas simples.',
            ],
            [
                'title' => 'Optimización de Cuota apiperu.dev',
                'body' => 'MiPromo almacena en caché durante 24 horas las consultas exitosas por DNI y aplica rate limiting público (20 peticiones/minuto), evitando consumo excesivo de consultas en tu plan.',
            ],
            [
                'title' => 'Respaldo Continuo (Backups cPanel)',
                'body' => 'Programa respaldos semanales de la base de datos MySQL y de la carpeta public/images/ desde cPanel / JetBackup antes de actualizar promociones masivas o lanzar campañas de alta demanda.',
            ],
            [
                'title' => 'Atención Comercial Ágil vía WhatsApp (959199368)',
                'body' => 'Todos los enlaces de contacto de la tienda, comprobantes y alertas redirigen directamente a https://wa.me/51959199368 para cerrar pedidos corporativos y validar comprobantes al instante.',
            ],
        ];
    }
}
