@extends('layouts.admin')

@section('heading', 'Contenido, Noticias y Banners de Temporada')
@section('subheading', 'Módulo L: Blog institucional, colecciones de temporada (Aniversarios, Fiestas Patrias, Navidad) y SEO')

@section('content')
<div class="space-y-8">
    <div class="grid lg:grid-cols-2 gap-6">
        <form action="{{ route('admin.content.posts.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            @csrf
            <h2 class="font-extrabold text-[#0F2D4A]">Publicar Noticia / Artículo</h2>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <input type="text" name="title" required placeholder="Titular de la noticia" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                <select name="category" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="noticia">Noticia Plataforma</option>
                    <option value="aniversario">Aniversario / Bodas de Plata</option>
                    <option value="comunidad">Historia de Comunidad</option>
                    <option value="guia">Guía para Delegados</option>
                </select>
                <select name="institution_id" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="">Todas las instituciones</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->short_name }}</option>
                    @endforeach
                </select>
            </div>
            <input type="text" name="excerpt" required placeholder="Resumen corto (SEO description)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm">
            <textarea name="body" required rows="3" placeholder="Contenido completo..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Publicar Artículo</button>
        </form>

        <form action="{{ route('admin.content.banners.store') }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            @csrf
            <h2 class="font-extrabold text-[#0F2D4A]">Nuevo Banner / Colección de Temporada</h2>
            <div class="grid sm:grid-cols-2 gap-3 text-sm">
                <input type="text" name="title" required placeholder="Título del banner" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="subtitle" required placeholder="Subtítulo promocional" class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="badge_text" placeholder="Etiqueta (ej. Fiestas Patrias)" class="px-3 py-2 rounded-xl border border-slate-300">
                <select name="season" class="px-3 py-2 rounded-xl border border-slate-300">
                    <option value="aniversario">Aniversarios Escolares</option>
                    <option value="fiestas_patrias">Fiestas Patrias</option>
                    <option value="navidad">Campaña Navidad</option>
                    <option value="reencuentro">Reencuentros</option>
                </select>
                <input type="text" name="cta_label" required value="Explorar colección" class="px-3 py-2 rounded-xl border border-slate-300">
                <input type="text" name="cta_url" required value="/tienda" class="px-3 py-2 rounded-xl border border-slate-300">
                <div class="flex items-center gap-2 text-xs"><span>Color fondo:</span><input type="color" name="bg_color" value="#0F2D4A"></div>
            </div>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold">Crear Banner</button>
        </form>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Artículos Publicados</h2>
            @foreach($posts as $post)
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                    <div>
                        <a href="{{ route('blog.show', $post->slug) }}" class="font-bold text-[#0F2D4A] hover:underline">{{ $post->title }}</a>
                        <p class="text-slate-500">{{ ucfirst($post->category) }} · {{ $post->institution?->short_name ?? 'Global' }}</p>
                    </div>
                    <form action="{{ route('admin.content.posts.toggle', $post->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-2.5 py-1 rounded-full font-bold {{ $post->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                            {{ $post->is_published ? 'Publicado' : 'Borrador' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
            <h2 class="font-extrabold text-[#0F2D4A]">Banners de Temporada</h2>
            @foreach($banners as $banner)
                <div class="p-3.5 rounded-xl text-white flex items-center justify-between gap-3 text-xs" style="background-color: {{ $banner->bg_color }}">
                    <div>
                        <p class="font-bold">{{ $banner->title }}</p>
                        <p class="text-white/80">{{ $banner->subtitle }}</p>
                    </div>
                    <form action="{{ route('admin.content.banners.toggle', $banner->id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-2.5 py-1 rounded-full bg-white/20 font-bold">
                            {{ $banner->is_active ? 'Activo' : 'Oculto' }}
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
