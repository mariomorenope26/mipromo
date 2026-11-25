@extends('layouts.app')

@section('title', 'Noticias y Colecciones — MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    <div>
        <span class="text-xs font-bold uppercase tracking-wider text-[#1D70B8]">Módulo L · Blog y Novedades</span>
        <h1 class="text-3xl font-extrabold text-[#0F2D4A] mt-1">Noticias de Comunidades e Instituciones</h1>
        <p class="text-sm text-slate-600 mt-1">Historias de promociones, aniversarios, lanzamientos de temporada y guías para delegados.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        @foreach($posts as $post)
            <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm flex flex-col">
                <img src="{{ asset(ltrim($post->image_path ?? 'images/products/anuario.jpg', '/')) }}" alt="{{ $post->title }}" class="h-44 w-full object-cover">
                <div class="p-5 flex-1 flex flex-col justify-between gap-4">
                    <div>
                        <span class="px-2.5 py-0.5 rounded-full bg-sky-50 text-[#1D70B8] text-[11px] font-bold uppercase">{{ $post->category }}</span>
                        <h2 class="mt-2 font-extrabold text-lg text-[#0F2D4A] leading-snug">
                            <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-[#1D70B8]">{{ $post->title }}</a>
                        </h2>
                        <p class="mt-1.5 text-xs text-slate-600 line-clamp-3">{{ $post->excerpt }}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $post->published_at?->format('d/m/Y') }}</span>
                        <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-[#1D70B8] hover:underline">Leer artículo →</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    <div>{{ $posts->links() }}</div>
</div>
@endsection
