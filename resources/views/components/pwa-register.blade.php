<div x-data="{
        showBanner: false,
        iosTip: false,
        deferredPrompt: null,
        init() {
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
                });
            }

            const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
            if (isStandalone || localStorage.getItem('mipromo_pwa_dismissed') === '1') {
                return;
            }

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.showBanner = true;
            });

            const ua = window.navigator.userAgent || '';
            const isIos = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            const isSafari = /Safari/.test(ua) && !/CriOS|FxiOS|EdgiOS/.test(ua);
            if (isIos && isSafari) {
                this.iosTip = true;
                this.showBanner = true;
            }
        },
        async installApp() {
            if (!this.deferredPrompt) return;
            this.deferredPrompt.prompt();
            await this.deferredPrompt.userChoice;
            this.deferredPrompt = null;
            this.showBanner = false;
        },
        dismiss() {
            localStorage.setItem('mipromo_pwa_dismissed', '1');
            this.showBanner = false;
        }
     }"
     x-show="showBanner"
     x-cloak
     class="fixed bottom-20 left-4 right-4 sm:left-auto sm:right-5 sm:max-w-sm z-40 print:hidden">
    <div class="rounded-2xl bg-[#0F2D4A] text-white border border-sky-400/30 shadow-2xl p-3.5 flex items-center gap-3">
        <img src="{{ asset('icons/icon-192x192.png') }}" alt="App MiPromo" class="w-11 h-11 rounded-xl bg-white p-1 shrink-0">
        <div class="min-w-0 flex-1">
            <p class="text-xs font-extrabold text-amber-300 leading-tight">Instala MiPromo App</p>
            <p x-show="!iosTip" class="text-[11px] text-slate-200 leading-snug mt-0.5">Acceso directo desde tu celular o tablet.</p>
            <p x-show="iosTip" class="text-[11px] text-slate-200 leading-snug mt-0.5">En Safari toca <strong>Compartir</strong> y elige <strong>“Añadir a pantalla de inicio”</strong>.</p>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button x-show="!iosTip" type="button" @click="installApp()" class="px-3 py-1.5 rounded-xl bg-amber-400 hover:bg-amber-300 text-[#0F2D4A] text-xs font-extrabold">
                Instalar
            </button>
            <button type="button" @click="dismiss()" aria-label="Cerrar aviso" class="p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-white/10 text-xs">
                ✕
            </button>
        </div>
    </div>
</div>
