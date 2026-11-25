@extends('layouts.app')

@section('title', $post->title.' — Noticias MiPromo')

@section('content')
<article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-6">
    <a href="{{ route('blog.index') }}" class="text-xs font-bold text-[#1D70B8] hover:underline">← Volver a Noticias</a>
    <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm">
        <img src="{{ asset(ltrim($post->image_path ?? 'images/products/anuario.jpg', '/')) }}" alt="{{ $post->title }}" class="w-full h-72 object-cover">
        <div class="p-8 space-y-5">
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="px-3 py-1 rounded-full bg-sky-50 text-[#1D70B8] font-bold uppercase">{{ $post->category }}</span>
                @if($post->institution)
                    <span class="px-3 py-1 rounded-full text-white font-bold" style="background-color: {{ $post->institution->primary_color }}">{{ $post->institution->short_name }}</span>
                @endif
                <span class="text-slate-500">{{ $post->published_at?->format('d/m/Y') }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-[#0F2D4A]">{{ $post->title }}</h1>
            <p class="text-base text-slate-600 font-medium">{{ $post->excerpt }}</p>
            <div class="prose max-w-none text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $post->body }}</div>

            <div class="pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                <span class="text-slate-500">Publicado por: <strong>{{ $post->author?->name ?? 'Equipo Editorial MiPromo' }}</strong></span>
                <a href="https://wa.me/?text={{ urlencode($post->title.' - '.url()->current()) }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold">
                    Compartir en WhatsApp ↗
                </a>
            </div>
        </div>
    </div>
</article>
@endsection
