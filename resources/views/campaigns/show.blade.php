@extends('layouts.app')

@section('title', $campaign->title.' — Campaña Grupal')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">
    <div class="grid lg:grid-cols-12 gap-8">
        <div class="lg:col-span-7 space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 space-y-5 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold text-white" style="background-color: {{ $campaign->institution->primary_color }}">
                        {{ $campaign->institution->name }}
                    </span>
                    @if($campaign->promotion)
                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-bold">Promoción {{ $campaign->promotion->year }}</span>
                    @endif
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">{{ $campaign->statusLabel() }}</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A]">{{ $campaign->title }}</h1>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $campaign->description }}</p>

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5 space-y-3">
                    <div class="flex justify-between text-sm font-bold">
                        <span>Meta grupal: {{ $campaign->current_units }} / {{ $campaign->target_units }} unidades</span>
                        <span class="text-[#1D70B8]">{{ $campaign->progressPercent() }}%</span>
                    </div>
                    <div class="w-full h-3.5 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full rounded-full {{ $campaign->progressPercent() >= 100 ? 'bg-emerald-500' : 'bg-[#1D70B8]' }}" style="width: {{ $campaign->progressPercent() }}%"></div>
                    </div>
                    <div class="flex flex-wrap justify-between text-xs text-slate-500">
                        <span>Faltan {{ $campaign->unitsRemaining() }} unidades para cerrar meta</span>
                        <span>Cierre: {{ $campaign->ends_at->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-4 pt-2">
                    <img src="{{ asset(ltrim($campaign->product->image_path ?? 'images/products/casaca.jpg', '/')) }}" alt="{{ $campaign->product->name }}" class="w-24 h-24 rounded-2xl bg-slate-50 object-cover border border-slate-200">
                    <div>
                        <p class="text-xs text-slate-500">Producto oficial de campaña</p>
                        <p class="font-bold text-slate-900">{{ $campaign->product->name }}</p>
                        <p class="text-sm font-extrabold text-[#0F2D4A] mt-1">Precio grupal: S/ {{ number_format($campaign->unit_price, 2) }}</p>
                        <p class="text-xs text-emerald-700 font-semibold">Reserva con {{ $campaign->deposit_percent }}% de adelanto (S/ {{ number_format($campaign->unit_price * ($campaign->deposit_percent / 100), 2) }})</p>
                    </div>
                </div>
            </div>

            {{-- Participantes --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                <h2 class="text-lg font-extrabold text-[#0F2D4A]">Participantes Registrados ({{ $campaign->participants->count() }})</h2>
                <div class="divide-y divide-slate-100 text-sm">
                    @forelse($campaign->participants as $part)
                        <div class="py-3 flex items-center justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-800">{{ $part->participant_name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $part->quantity }} ud(s) · {{ $part->variant?->label() }}
                                    @if($part->custom_name) · Bordado: <strong>{{ $part->custom_name }}</strong> @endif
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $part->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $part->payment_status === 'paid' ? 'Pagado' : ($part->payment_status === 'partial' ? 'Adelanto' : 'Pendiente') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-500">Sé el primero en reservar tu unidad.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Formulario Unirse a Campaña --}}
        <div class="lg:col-span-5">
            <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm sticky top-24 space-y-4">
                <h2 class="text-xl font-extrabold text-[#0F2D4A]">Reservar mi Unidad en Campaña</h2>
                <form action="{{ route('campaigns.join', $campaign->slug) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nombre completo</label>
                        <input type="text" name="participant_name" required value="{{ old('participant_name', auth()->user()?->name) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Correo</label>
                            <input type="email" name="participant_email" required value="{{ old('participant_email', auth()->user()?->email) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Celular / WhatsApp</label>
                            <input type="text" name="participant_phone" required value="{{ old('participant_phone', auth()->user()?->phone) }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Talla / Variante</label>
                            <select name="variant_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                                @foreach($campaign->product->variants as $variant)
                                    <option value="{{ $variant->id }}">{{ $variant->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cantidad</label>
                            <input type="number" name="quantity" value="1" min="1" max="20" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nombre / Apellido bordado</label>
                            <input type="text" name="custom_name" placeholder="Ej. MORENO" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Año Promoción</label>
                            <input type="text" name="custom_year" value="{{ $campaign->promotion?->year }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Modalidad de pago</label>
                            <select name="payment_plan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                                <option value="deposit">Adelanto {{ $campaign->deposit_percent }}%</option>
                                <option value="full">Pago completo 100%</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Método</label>
                            <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                                <option value="yape">Yape (959199368)</option>
                                <option value="plin">Plin (959199368)</option>
                                <option value="card_culqi">Tarjeta (Aprobación directa)</option>
                                <option value="bank_transfer">Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">N.° de operación (si aplica Yape/Plin)</label>
                        <input type="text" name="reference_code" placeholder="Ej. YAPE-123456" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm">
                        <a href="https://wa.me/51959199368?text={{ urlencode('Hola Grupo JMSPERU, envío constancia Yape/Plin para la campaña: '.$campaign->title) }}" target="_blank" rel="noopener noreferrer" class="inline-block mt-1.5 text-xs font-bold text-emerald-700 hover:underline">
                            💬 Enviar constancia Yape/Plin por WhatsApp (959199368) ↗
                        </a>
                    </div>

                    <button type="submit" class="w-full py-3.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white font-extrabold text-sm transition">
                        Confirmar Participación en Campaña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
