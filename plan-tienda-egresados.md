# Plan de Trabajo: Plataforma de Tienda en Línea y Comunidades de Ex-Alumnos

**Nombre tentativo:** Plataforma "Egresados Store" (nombre comercial por definir)
**Tipo:** Marketplace / tienda en línea multi-institución + red de comunidades de ex-alumnos
**Alcance geográfico:** Local (por ciudad/región) y nacional (Perú)
**Documento:** Plan de trabajo funcional y técnico (sin código fuente)
**Fecha de elaboración:** 20 de noviembre de 2026

---

## 1. Resumen del proyecto

Una plataforma web que combina dos ideas:

1. **Tienda en línea tipo Shopify**: cada institución educativa (o cada promoción) tiene su propia tienda o catálogo con productos personalizados (recordatorios, souvenirs, casacas, gorros, polos, tazas, llaveros, etc.), con carrito, pagos y envíos a nivel nacional.
2. **Directorio y comunidades de ex-alumnos**: base de datos de egresados organizada por **colegio → año de promoción → aula/sección**, donde cada grupo tiene su comunidad, eventos y canal de comunicación.

**Idea de valor:** la comunidad genera la demanda (una promoción organiza su "polo de los 20 años") y la tienda monetiza esa demanda. Cada comunidad puede abrir **campañas/pedidos grupales** con meta mínima de unidades.

---

## 2. Objetivos

### Objetivo general
Construir una plataforma web escalable que permita vender productos personalizados a ex-alumnos de múltiples instituciones y mantener comunidades organizadas por colegio, promoción y aula.

### Objetivos específicos
- Permitir que cada institución tenga su espacio con identidad visual propia (escudo, colores, lema).
- Registrar y validar ex-alumnos con sus datos de egreso.
- Crear comunidades automáticas y manuales (colegio, promoción, aula, grupos de interés).
- Vender productos con catálogo, variantes (tallas, colores), stock y pagos locales.
- Habilitar **campañas grupales** de pedidos por promoción.
- Gestionar envíos locales y nacionales y emitir comprobantes electrónicos.
- Ofrecer paneles de administración por rol (plataforma, institución, promoción, vendedor).

---

## 3. Actores y roles

| Rol | Descripción | Permisos principales |
|---|---|---|
| **Super administrador** | Dueño de la plataforma | Todo: instituciones, comisiones, usuarios, reportes globales |
| **Administrador de institución** | Representante del colegio o asociación de ex-alumnos | Valida egresados, gestiona su comunidad y catálogo propio |
| **Delegado de promoción** | Líder de una promoción | Administra comunidad de su promoción, crea campañas grupales |
| **Delegado de aula** | Responsable de una sección | Gestiona listado y comunicación del aula |
| **Vendedor / Proveedor** | Quien fabrica o provee productos | Gestiona productos, stock, pedidos asignados |
| **Ex-alumno (miembro)** | Usuario registrado | Perfil, comunidades, compras, participación en campañas |
| **Visitante / comprador** | Compra sin pertenecer a una comunidad | Navega y compra productos públicos |
| **Soporte / moderador** | Atención y control de contenido | Tickets, moderación, reportes |

---

## 4. Módulos del sistema

### Módulo A. Tienda en línea (e-commerce)
- Catálogo de productos con categorías (ropa, accesorios, recordatorios, souvenirs, papelería, etc.).
- Variantes: talla, color, material, modelo.
- Personalización por institución/promoción (escudo, año, nombre de la promoción, texto).
- Gestión de inventario y stock por variante, con alertas de stock bajo.
- Carrito de compras y checkout (invitado o con cuenta).
- Cupones, descuentos por volumen y precios especiales para miembros.
- Productos bajo pedido (producción por campaña) y productos en stock.
- Listas de deseos y productos relacionados.
- Reseñas y calificaciones de productos.

### Módulo B. Campañas grupales y preventas
- Creación de campañas por promoción o aula (ej. "Casaca Promoción 2005").
- Meta mínima de unidades y fecha límite.
- Pago anticipado, reserva con adelanto o pago contra entrega según configuración.
- Seguimiento de la campaña (cuántos faltan, cuántos han pagado).
- Cierre de campaña y generación de orden de producción consolidada.
- Notificaciones automáticas a los miembros.

### Módulo C. Directorio de ex-alumnos
- Registro de egresados con: nombres, documento de identidad, institución, año de ingreso/egreso, promoción, aula/sección, ciudad actual, contacto, profesión, empresa.
- Verificación del egresado (validación por delegado, administrador o documento de respaldo).
- Importación masiva desde Excel/CSV para cargar listados históricos de promociones.
- Control de privacidad por campo (qué datos son visibles a quién).
- Búsqueda avanzada por colegio, promoción, aula, ciudad o profesión.
- Detección de duplicados.
- Línea de tiempo de eventos y perfil de contribución.

### Módulo D. Comunidades
- Jerarquía automática: **Institución → Promoción → Aula**.
- Grupos adicionales: por ciudad, por profesión, por deporte, por generación, etc.
- Muro de publicaciones, comentarios, fotos y álbumes históricos.
- Eventos (reuniones, aniversarios, bodas de plata) con confirmación de asistencia.
- Mensajería interna y anuncios masivos (correo, WhatsApp, notificaciones).
- Encuestas y votaciones (por ejemplo, para elegir el diseño de un polo).
- Cuotas y colectas para eventos o fines solidarios.
- Reglas de moderación y reporte de contenido.

### Módulo E. Instituciones educativas
- Alta de instituciones (nombre, tipo, ubicación, código modular, escudo, colores, lema).
- Landing/página propia de cada institución dentro de la plataforma.
- Historial y datos institucionales (fundación, lema, himno, galería).
- Convenios o comisión institucional (porcentaje de ventas que retorna al colegio o asociación).

### Módulo F. Pagos
- Pasarela de pago con tarjetas (Culqi, Izipay, Niubiz, Mercado Pago u otra, a evaluar).
- Billeteras: Yape y Plin (vía pasarela o validación manual de comprobante).
- Transferencia bancaria y depósito con carga de voucher.
- Pago en efectivo/contra entrega en zonas habilitadas.
- Conciliación de pagos y estado de cada transacción.
- Pagos parciales (adelanto + saldo) para campañas.

### Módulo G. Logística y envíos
- Zonas y tarifas: local (recojo o delivery propio) y nacional (courier).
- Integración o tarifario con couriers nacionales (Olva Courier, Shalom, Serpost, etc.).
- Recojo en punto de entrega por institución o delegado.
- Seguimiento del pedido con número de guía.
- Consolidación de envíos por campaña (un envío masivo al delegado).
- Gestión de devoluciones y cambios.

### Módulo H. Facturación y comprobantes
- Emisión de boletas y facturas electrónicas conforme a SUNAT (vía integración con facturador electrónico).
- Notas de crédito/débito.
- Series por tienda o por vendedor, según modelo de negocio.
- Reportes tributarios y de ventas.

### Módulo I. Marketplace multi-vendedor (fase avanzada)
- Registro de proveedores/talleres que ofrecen sus productos.
- Comisión de plataforma por venta.
- Liquidaciones periódicas a vendedores e instituciones.
- Panel por vendedor: pedidos, stock, ganancias.

### Módulo J. Administración, reportes y analítica
- Dashboards por rol (plataforma, institución, promoción, vendedor).
- Reportes: ventas, productos más vendidos, campañas, crecimiento de comunidades, egresados por colegio/promoción.
- Exportación a Excel/PDF.
- Auditoría de acciones críticas.

### Módulo K. Comunicación y notificaciones
- Correo transaccional y masivo.
- Notificaciones push/web y enlaces directos a WhatsApp.
- Plantillas de mensajes (bienvenida, recordatorio de pago, campaña por cerrar, evento).

### Módulo L. Contenido y marketing
- Blog/noticias de la plataforma e instituciones.
- Banners, secciones destacadas y colecciones por temporada (aniversarios, fiestas patrias, Navidad).
- SEO básico y compartir en redes sociales.
- Programa de referidos ("invita a tu promoción").

---

## 5. Modelo de datos (entidades principales, sin código)

- **Institución**: datos generales, identidad visual, ubicación, estado, convenios.
- **Promoción**: año, nombre, institución, delegado(s).
- **Aula/Sección**: grado, sección, promoción.
- **Ex-alumno**: datos personales, vínculo a institución, promoción y aula, estado de verificación, privacidad.
- **Usuario y Rol**: credenciales, rol, permisos, vínculo opcional a ex-alumno.
- **Comunidad / Grupo**: tipo (institución, promoción, aula, interés), miembros, moderadores.
- **Publicación, Comentario, Evento, Encuesta, Álbum**: contenido social de las comunidades.
- **Producto, Variante, Categoría, Imagen, Stock**: catálogo.
- **Diseño/Personalización**: plantillas gráficas ligadas a institución/promoción.
- **Campaña**: producto(s), meta, fechas, estado, participantes.
- **Carrito, Pedido, Detalle de pedido**: proceso de compra.
- **Pago y Transacción**: método, estado, referencia, conciliación.
- **Envío, Zona y Tarifa**: logística.
- **Comprobante electrónico**: serie, correlativo, estado ante SUNAT.
- **Vendedor/Proveedor y Liquidación**: marketplace.
- **Cupón/Descuento**: reglas promocionales.
- **Notificación y Mensaje**: comunicaciones.
- **Auditoría/Log**: trazabilidad.

**Relaciones clave:** Institución 1–N Promociones, Promoción 1–N Aulas, Aula 1–N Ex-alumnos; Promoción/Aula/Institución 1–1 Comunidad; Campaña N–1 Promoción; Pedido N–1 Usuario; Pedido 1–N Detalles; Pedido 1–N Pagos.

---

## 6. Arquitectura y tecnología propuesta

> Propuesta alineada con el stack habitual de trabajo; se puede ajustar.

- **Backend:** PHP con Laravel (API + panel web), arquitectura modular por dominios.
- **Base de datos:** MySQL (diseño preparado para multi-institución).
- **Frontend:** Blade/Livewire o Vue/Inertia para interfaces dinámicas; diseño responsive (mobile-first, ya que gran parte del público compra desde el celular).
- **Multi-tenencia:** una sola instalación con separación lógica por institución (identidad visual y datos aislados por tenant), con subdominios o rutas por colegio (ej. `colegio.plataforma.pe`).
- **Almacenamiento de archivos:** imágenes de productos, fotos de comunidad y documentos, con optimización y CDN.
- **Colas y tareas programadas:** envío de correos masivos, recordatorios de pago, cierre de campañas, reportes.
- **Caché y rendimiento:** caché de catálogo y páginas públicas.
- **Seguridad:** autenticación segura, roles/permisos, protección contra fraude, copias de seguridad automáticas, HTTPS, registro de auditoría.
- **Integraciones:** pasarelas de pago, facturador SUNAT, couriers, WhatsApp, correo transaccional.
- **Infraestructura:** VPS/nube con ambiente de pruebas (staging) y producción, respaldos diarios y monitoreo.

---

## 7. Plan por fases

### Fase 0. Descubrimiento y definición (2 a 3 semanas)
- Reuniones con 2 o 3 instituciones/promociones piloto.
- Definir modelo de negocio: comisiones, quién produce, quién vende, quién factura.
- Levantamiento de requisitos y priorización (MVP vs. fases posteriores).
- Definir marca, nombre y dominio.
- Investigar proveedores de producción (polos, casacas, gorros, souvenirs) y couriers.
- Entregable: documento de requisitos, mapa de módulos y prototipos de baja fidelidad.

### Fase 1. Diseño UX/UI y arquitectura (3 semanas)
- Diseño de flujos: registro de ex-alumno, compra, campaña grupal, comunidad.
- Diseño visual del sistema base y de las "tiendas por institución" (plantilla personalizable).
- Diseño de base de datos y arquitectura multi-institución.
- Definir reglas de privacidad y consentimiento de datos.
- Entregable: prototipos de alta fidelidad, modelo de datos y guía de estilo.

### Fase 2. MVP núcleo (8 a 10 semanas)
**Objetivo:** validar el concepto con pocas instituciones.
- Autenticación, roles y permisos.
- Gestión de instituciones, promociones y aulas.
- Registro y verificación de ex-alumnos + importación masiva.
- Comunidades básicas (institución, promoción, aula) con muro, anuncios y miembros.
- Catálogo de productos con variantes y stock.
- Carrito, checkout y pedidos.
- Pagos básicos: pasarela de tarjeta + Yape/Plin con voucher + transferencia.
- Panel de administración inicial por rol.
- Correos transaccionales.
- Entregable: plataforma funcional en producción para pilotos.

### Fase 3. Campañas, logística y facturación (6 a 8 semanas)
- Módulo de campañas grupales y preventas con metas y fechas.
- Pagos parciales y seguimiento de cobranza.
- Zonas de envío, tarifas, couriers y recojo en puntos.
- Facturación electrónica SUNAT (boletas y facturas).
- Cupones y descuentos.
- Notificaciones automáticas (correo + WhatsApp enlazado).
- Entregable: ciclo completo "comunidad → campaña → pago → producción → envío → comprobante".

### Fase 4. Comunidad avanzada (6 semanas)
- Eventos con confirmación de asistencia, encuestas y votaciones.
- Álbumes y galería histórica.
- Mensajería interna y segmentación de comunicaciones.
- Colectas y cuotas para eventos.
- Moderación y reportes de contenido.
- Directorio con búsqueda avanzada y privacidad granular.

### Fase 5. Marketplace y escalamiento nacional (8 semanas)
- Registro de proveedores/talleres y panel de vendedor.
- Comisiones, liquidaciones y reportes financieros.
- Catálogos por región, tarifas nacionales y puntos de entrega.
- Programa de referidos y herramientas de marketing.
- Optimización de rendimiento y SEO.
- Posible app móvil o PWA instalable.

### Fase 6. Lanzamiento y crecimiento (continuo)
- Campaña de lanzamiento por instituciones piloto.
- Captación de colegios y asociaciones de ex-alumnos.
- Soporte, mejora continua y analítica de comportamiento.
- Nuevas líneas de producto y temporadas (aniversarios, Fiestas Patrias, Navidad).

---

## 8. Cronograma resumido (referencial)

| Fase | Duración estimada | Acumulado |
|---|---|---|
| 0. Descubrimiento | 2–3 semanas | ~3 sem |
| 1. Diseño y arquitectura | 3 semanas | ~6 sem |
| 2. MVP núcleo | 8–10 semanas | ~16 sem |
| 3. Campañas, logística, facturación | 6–8 semanas | ~24 sem |
| 4. Comunidad avanzada | 6 semanas | ~30 sem |
| 5. Marketplace y escala nacional | 8 semanas | ~38 sem |
| 6. Lanzamiento y crecimiento | Continuo | — |

*Los tiempos asumen un equipo pequeño y pueden variar según el alcance acordado con cada piloto.*

---

## 9. Prioridad de funcionalidades (MoSCoW)

**Imprescindible (Must):** instituciones/promociones/aulas, registro y verificación de ex-alumnos, comunidades básicas, catálogo con variantes, carrito y pedidos, pagos (tarjeta + Yape/Plin + transferencia), roles y paneles.

**Importante (Should):** campañas grupales, envíos nacionales, facturación SUNAT, cupones, notificaciones automáticas, importación masiva.

**Deseable (Could):** eventos, encuestas, colectas, galerías, reseñas, referidos, blog.

**Futuro (Won't por ahora):** marketplace multi-vendedor completo, app móvil nativa, programa de fidelización avanzado, pagos internacionales.

---

## 10. Modelo de negocio (opciones a definir)

- Margen sobre la venta de productos propios.
- Comisión por venta a proveedores externos (marketplace).
- Comisión o aporte a la institución/asociación por cada venta de su comunidad.
- Plan de suscripción para instituciones (tienda personalizada, más almacenamiento, soporte).
- Servicios extra: diseño de merchandising, organización de campañas, impresión personalizada.
- Publicidad o auspicios locales dentro de las comunidades (fase posterior).

---

## 11. Aspectos legales y de cumplimiento

- **Protección de datos personales (Ley N.° 29733 y su reglamento):** consentimiento informado, finalidad clara, política de privacidad, términos y condiciones, derechos ARCO, registro de bancos de datos si corresponde.
- **Menores de edad:** la plataforma debe orientarse a egresados adultos; definir criterios para evitar el registro de menores.
- **Derechos de marca e imagen:** autorización de la institución para uso de escudo, nombre y lema en los productos.
- **Comercio electrónico y consumidor:** políticas de cambios, devoluciones y libro de reclamaciones virtual.
- **Tributario:** emisión de comprobantes electrónicos y definición de régimen según el modelo de negocio.
- **Moderación de contenido:** normas de convivencia y mecanismo de reporte.

---

## 12. Seguridad y privacidad

- Roles y permisos estrictos; cada institución solo ve sus propios datos.
- Control de visibilidad por campo en el perfil del egresado.
- Cifrado en tránsito (HTTPS) y protección de datos sensibles.
- Respaldos automáticos diarios con pruebas de restauración.
- Registro de auditoría de acciones administrativas.
- Prevención de fraude en pagos y validación de vouchers.
- Política de retención y eliminación de datos a solicitud del titular.

---

## 13. Equipo sugerido

| Perfil | Responsabilidad |
|---|---|
| Líder de proyecto / Product owner | Prioridades, relación con instituciones, negocio |
| Desarrollador backend | Lógica, base de datos, integraciones |
| Desarrollador frontend | Interfaces responsive, experiencia de usuario |
| Diseñador UX/UI | Flujos, prototipos y marca |
| Especialista en operaciones/logística | Proveedores, producción, couriers |
| Atención al cliente y comunidad | Soporte, moderación y onboarding de instituciones |
| QA / pruebas | Validación funcional y de pagos |

*Una sola persona puede cubrir varios roles en las primeras fases.*

---

## 14. Riesgos y mitigación

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Baja adopción inicial de ex-alumnos | Alto | Empezar con 2–3 promociones piloto muy activas y delegados comprometidos |
| Datos de egresados incompletos o desactualizados | Medio | Importación masiva + validación por delegados + campañas de actualización |
| Incumplimiento de la ley de datos personales | Alto | Consentimiento explícito, política de privacidad, asesoría legal |
| Problemas de producción o entrega tardía | Alto | Proveedores calificados, plazos claros en campañas, comunicación constante |
| Costos de envío nacional elevados | Medio | Consolidar envíos por campaña, puntos de recojo, tarifas negociadas |
| Fraude o pagos no conciliados | Medio | Pasarela oficial, validación de vouchers, conciliación diaria |
| Alcance demasiado amplio | Alto | MVP acotado y priorización por fases |
| Rechazo de instituciones por uso de marca | Medio | Convenios y autorización formal; aporte económico a la institución |

---

## 15. Indicadores de éxito (KPIs)

- N.° de instituciones y promociones activas.
- N.° de ex-alumnos registrados y verificados.
- Tasa de actividad mensual de las comunidades.
- N.° de campañas creadas y porcentaje que alcanza su meta.
- Ventas totales, ticket promedio y tasa de conversión.
- Porcentaje de pedidos entregados a tiempo.
- Satisfacción del cliente (reseñas / NPS).
- Ventas por región (local vs. nacional).

---

## 16. Plan de lanzamiento piloto

1. Seleccionar 2–3 instituciones con asociaciones de ex-alumnos activas (idealmente una local y una de otra región).
2. Cargar listados históricos de 3–5 promociones por colegio.
3. Diseñar 3–5 productos base por institución (polo, gorro, casaca, souvenir).
4. Lanzar una **campaña grupal** por promoción como prueba de concepto.
5. Medir resultados, recoger feedback y ajustar antes de escalar.
6. Replicar el modelo a nuevas instituciones con un "kit de incorporación" (checklist, plantillas de comunicación, guía para delegados).

---

## 17. Próximos pasos inmediatos

1. Validar este plan y decidir el nombre y modelo de negocio.
2. Elegir las instituciones y promociones piloto.
3. Definir el alcance exacto del MVP (Fase 2).
4. Contactar proveedores de producción y couriers para cotizaciones.
5. Iniciar la Fase 0 (descubrimiento) y la Fase 1 (diseño).

---

## 18. Decisiones pendientes

- ¿Quién produce y quién emite el comprobante: la plataforma o cada vendedor/institución?
- ¿Se manejará stock propio, producción bajo pedido o ambos?
- ¿Qué pasarela de pago se usará primero?
- ¿Una tienda por institución con subdominio propio o un catálogo único con filtros?
- ¿Cómo se verificará a los ex-alumnos (delegado, documentos, ambos)?
- ¿Qué porcentaje retorna a las instituciones o asociaciones?
- ¿Se incluirá app móvil o bastará con una web responsive/PWA?
