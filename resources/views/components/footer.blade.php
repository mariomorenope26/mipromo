<footer class="bg-[#0F2D4A] text-slate-200 border-t border-slate-800 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2 space-y-3">
                <div class="inline-flex items-center bg-white rounded-xl px-3.5 py-2 shadow-sm">
                    <img src="{{ asset('images/logo_jmsperu.png') }}" alt="Grupo JMSPERU" class="h-10 w-auto object-contain">
                </div>
                <p class="text-sm text-slate-300 max-w-md leading-relaxed">
                    Plataforma peruana de tiendas en línea personalizadas, campañas grupales y comunidades organizadas de ex-alumnos por institución, promoción y aula.
                </p>
                <div class="flex flex-wrap gap-2 text-[11px] font-semibold">
                    <span class="px-2.5 py-1 rounded-full bg-white/10 text-sky-200">Yape / Plin</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/10 text-sky-200">Tarjetas</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/10 text-sky-200">Comprobantes SUNAT</span>
                    <span class="px-2.5 py-1 rounded-full bg-white/10 text-sky-200">Envíos Olva &amp; Shalom</span>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-sky-300 mb-3">Plataforma</h3>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li><a href="{{ route('store.index') }}" class="hover:text-white">Tienda en Línea</a></li>
                    <li><a href="{{ route('institutions.index') }}" class="hover:text-white">Colegios e Instituciones</a></li>
                    <li><a href="{{ route('campaigns.index') }}" class="hover:text-white">Campañas Grupales</a></li>
                    <li><a href="{{ route('communities.index') }}" class="hover:text-white">Comunidades de Egresados</a></li>
                    <li><a href="{{ route('directory.index') }}" class="hover:text-white">Directorio Nacional</a></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-white">Noticias y Colecciones</a></li>
                </ul>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-sky-300 mb-3">Legal y Soporte</h3>
                <ul class="space-y-2 text-sm text-slate-300">
                    <li><a href="{{ route('orders.track') }}" class="hover:text-white">Seguimiento de Pedidos</a></li>
                    @auth
                        <li><a href="{{ route('manual.index') }}" class="hover:text-white font-semibold text-sky-200">Manual de Uso en Línea</a></li>
                        <li><a href="{{ route('manual.pdf') }}" class="hover:text-white">Descargar Manual en PDF ↓</a></li>
                    @endauth
                    <li><a href="{{ route('legal.privacy') }}" class="hover:text-white">Privacidad (Ley N.° 29733)</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="hover:text-white">Términos y Devoluciones</a></li>
                    <li><a href="{{ route('legal.complaints') }}" class="hover:text-white font-semibold text-amber-300">Libro de Reclamaciones Virtual</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-300">
            <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU / MiPromo, me contacto desde la tienda web.') }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-white hover:text-sky-300 text-center sm:text-left transition">
                Todos los derechos reservados son del Grupo JMSPERU :: Contacto 959199368
            </a>
            <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU / MiPromo, solicito atención directa por WhatsApp.') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-semibold transition">
                <span>💬 WhatsApp directo: 959199368 ↗</span>
            </a>
        </div>
    </div>
</footer>
