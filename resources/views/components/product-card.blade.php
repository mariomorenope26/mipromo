@props(['product'])

@php
    $totalStock = $product->totalStock();
    $firstVariant = $product->variants->first();
    $memberSavings = $product->member_price && (float) $product->member_price < (float) $product->base_price
        ? round((float) $product->base_price - (float) $product->member_price, 2)
        : 0;
@endphp

<article class="group bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
    <a href="{{ route('products.show', $product->slug) }}" class="relative block bg-slate-100 aspect-[4/3] overflow-hidden">
        <img src="{{ asset(ltrim($product->image_path ?? 'images/products/polo.jpg', '/')) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
            @if($product->institution)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold text-white shadow-sm" style="background-color: {{ $product->institution->primary_color }}">
                    @if($product->institution->logo_path)
                        <img src="{{ asset(ltrim($product->institution->logo_path, '/')) }}" alt="{{ $product->institution->short_name }}" class="w-3.5 h-3.5 object-contain bg-white rounded-full p-0.5">
                    @endif
                    {{ $product->institution->short_name }}
                </span>
            @else
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[#1D70B8] text-white shadow-sm">
                    Multi-Colegio
                </span>
            @endif
            @if($product->is_preorder_only)
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Bajo Pedido</span>
            @endif
        </div>

        @if($totalStock > 0 && $totalStock <= 5)
            <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-600 text-white shadow-sm">
                🔥 ¡Últimas {{ $totalStock }} uds!
            </span>
        @endif

        @if($product->hasVerifiedVendorBadge())
            <span class="absolute bottom-2.5 right-2.5 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-600/95 text-white shadow-sm backdrop-blur">
                ✓ Proveedor Verificado
            </span>
        @endif
    </a>

    <div class="p-4 flex-1 flex flex-col justify-between gap-3">
        <div>
            <div class="flex items-center justify-between gap-2">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#1D70B8]">{{ $product->category->name }}</p>
                @if($memberSavings > 0)
                    <span class="text-[10px] font-extrabold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">
                        Ahorra S/ {{ number_format($memberSavings, 0) }}
                    </span>
                @endif
            </div>
            <h3 class="mt-1 font-bold text-slate-900 leading-snug line-clamp-2">
                <a href="{{ route('products.show', $product->slug) }}" class="hover:text-[#1D70B8]">{{ $product->name }}</a>
            </h3>
            <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $product->short_description }}</p>
        </div>

        <div class="pt-3 border-t border-slate-100 space-y-2.5">
            <div class="flex items-end justify-between gap-2">
                <div>
                    <div class="flex flex-wrap items-baseline gap-1.5">
                        <span class="text-lg font-extrabold text-[#0F2D4A]">S/ {{ number_format($product->base_price, 2) }}</span>
                        @if($product->member_price)
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">Ex-Alumno S/ {{ number_format($product->member_price, 2) }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        {{ $product->variants->count() }} variantes · Stock: {{ $totalStock }} · -10% desde 12 uds
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                @if($firstVariant && ($totalStock > 0 || $product->is_preorder_only))
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="w-full py-2 px-2.5 rounded-xl bg-[#1D70B8] hover:bg-[#0F2D4A] text-white text-xs font-extrabold transition">
                            + Agregar
                        </button>
                    </form>
                @else
                    <span class="w-full py-2 px-2.5 rounded-xl bg-slate-100 text-slate-400 text-xs font-bold text-center">Sin stock</span>
                @endif
                <a href="{{ route('products.show', $product->slug) }}" class="w-full text-center py-2 px-2.5 rounded-xl bg-slate-900 hover:bg-[#0F2D4A] text-white text-xs font-semibold transition">
                    Personalizar
                </a>
            </div>
        </div>
    </div>
</article>
