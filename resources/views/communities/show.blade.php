@extends('layouts.app')

@section('title', $community->name.' — Comunidad MiPromo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-8">
    {{-- Header --}}
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
        <div>
            <span class="px-3 py-1 rounded-full bg-sky-50 text-[#1D70B8] text-xs font-bold">{{ $community->typeLabel() }}</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2D4A] mt-2">{{ $community->name }}</h1>
            <p class="text-sm text-slate-600 mt-1 max-w-2xl">{{ $community->description }}</p>
            <p class="text-xs text-slate-500 mt-2">
                <strong>{{ $community->members_count }}</strong> miembros · <strong>{{ $community->posts_count }}</strong> publicaciones · <strong>{{ $community->events_count }}</strong> eventos
            </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            @auth
                <form action="{{ route('communities.join', $community->slug) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-sm font-bold transition {{ $isMember ? 'bg-slate-200 text-slate-800 hover:bg-slate-300' : 'bg-[#1D70B8] text-white hover:bg-[#0F2D4A]' }}">
                        {{ $isMember ? 'Miembro ✓ (Salir)' : '+ Unirme a esta comunidad' }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-[#1D70B8] text-white text-sm font-bold">Inicia sesión para participar</a>
            @endauth
        </div>
    </div>

    <div class="grid lg:grid-cols-12 gap-8">
        {{-- Muro Principal --}}
        <div class="lg:col-span-8 space-y-6">
            @auth
                <form action="{{ route('communities.posts.store', $community->slug) }}" method="POST" class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                    @csrf
                    <div class="grid sm:grid-cols-3 gap-3">
                        <select name="category" class="px-3 py-2 rounded-xl border border-slate-300 text-sm">
                            <option value="general">Publicación general</option>
                            <option value="anuncio">Anuncio oficial</option>
                            <option value="recuerdo">Foto / Recuerdo</option>
                            <option value="campana">Campaña grupal</option>
                            <option value="colecta">Colecta / Cuota</option>
                        </select>
                        <input type="text" name="title" placeholder="Título opcional..." class="sm:col-span-2 px-3 py-2 rounded-xl border border-slate-300 text-sm">
                    </div>
                    <textarea name="body" required rows="3" placeholder="Comparte novedades, recuerdos o convocatorias con tu comunidad..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm"></textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="px-5 py-2 rounded-xl bg-[#1D70B8] text-white text-xs font-bold">Publicar en el Muro</button>
                    </div>
                </form>
            @endauth

            @forelse($posts as $post)
                <article class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-slate-900">{{ $post->user->name }}</span>
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase">{{ $post->category }}</span>
                                @if($post->is_pinned)
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Fijado</span>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-400">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                        @auth
                            <form action="{{ route('communities.posts.report', $post->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="reason" value="Contenido inapropiado o spam">
                                <button type="submit" class="text-[11px] text-slate-400 hover:text-rose-600">Reportar</button>
                            </form>
                        @endauth
                    </div>

                    @if($post->title)
                        <h2 class="text-base font-extrabold text-[#0F2D4A]">{{ $post->title }}</h2>
                    @endif
                    <p class="text-sm text-slate-700 leading-relaxed">{{ $post->body }}</p>

                    <div class="flex items-center gap-4 pt-2 border-t border-slate-100 text-xs">
                        @auth
                            <form action="{{ route('communities.posts.like', $post->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="font-bold text-[#1D70B8] hover:underline">👍 Me gusta ({{ $post->likes_count }})</button>
                            </form>
                        @else
                            <span class="text-slate-500">👍 {{ $post->likes_count }} me gusta</span>
                        @endauth
                        <span class="text-slate-500">{{ $post->comments->count() }} comentarios</span>
                    </div>

                    {{-- Comentarios --}}
                    <div class="space-y-2.5 pl-4 border-l-2 border-slate-100">
                        @foreach($post->comments as $comment)
                            <div class="text-xs bg-slate-50 rounded-xl p-3">
                                <strong class="text-slate-800">{{ $comment->user->name }}:</strong>
                                <span class="text-slate-600">{{ $comment->body }}</span>
                            </div>
                        @endforeach

                        @auth
                            <form action="{{ route('communities.comments.store', $post->id) }}" method="POST" class="flex gap-2 pt-1">
                                @csrf
                                <input type="text" name="body" required placeholder="Escribe un comentario..." class="flex-1 px-3 py-1.5 rounded-xl border border-slate-300 text-xs">
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-bold">Enviar</button>
                            </form>
                        @endauth
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-sm text-slate-500">
                    Aún no hay publicaciones en esta comunidad.
                </div>
            @endforelse
        </div>

        {{-- Eventos y Encuestas --}}
        <aside class="lg:col-span-4 space-y-6">
            {{-- Encuestas --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                <h2 class="font-extrabold text-[#0F2D4A]">Encuestas y Votaciones</h2>
                @forelse($polls as $poll)
                    @php($totalVotes = max(1, $poll->options->sum('votes_count')))
                    <div class="space-y-3 border-t border-slate-100 pt-3 first:border-t-0 first:pt-0">
                        <p class="text-sm font-bold text-slate-800">{{ $poll->question }}</p>
                        @foreach($poll->options as $opt)
                            @php($pct = (int) round(($opt->votes_count / $totalVotes) * 100))
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span>{{ $opt->label }}</span>
                                    <span class="font-bold">{{ $pct }}% ({{ $opt->votes_count }})</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full bg-[#1D70B8]" style="width: {{ $pct }}%"></div>
                                </div>
                                @auth
                                    <form action="{{ route('communities.polls.vote', $poll->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="option_id" value="{{ $opt->id }}">
                                        <button type="submit" class="text-[11px] font-bold text-[#1D70B8] hover:underline">Votar por esta opción</button>
                                    </form>
                                @endauth
                            </div>
                        @endforeach
                    </div>
                @empty
                    <p class="text-xs text-slate-500">No hay encuestas activas.</p>
                @endforelse
            </div>

            {{-- Eventos --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                <h2 class="font-extrabold text-[#0F2D4A]">Próximos Eventos y Reencuentros</h2>
                @forelse($events as $event)
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2">
                        <p class="text-xs font-bold text-[#1D70B8]">{{ $event->starts_at->format('d/m/Y H:i') }}</p>
                        <h3 class="text-sm font-bold text-slate-900">{{ $event->title }}</h3>
                        <p class="text-xs text-slate-600">{{ $event->location }}</p>
                        <p class="text-xs font-semibold text-slate-700">{{ $event->rsvps->where('status', 'going')->count() }} confirmados</p>
                        @auth
                            <form action="{{ route('communities.events.rsvp', $event->id) }}" method="POST" class="flex gap-2 pt-1">
                                @csrf
                                <button type="submit" name="status" value="going" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">Asistiré</button>
                                <button type="submit" name="status" value="maybe" class="px-2.5 py-1 rounded-lg bg-slate-200 text-slate-700 text-[11px] font-bold">Tal vez</button>
                            </form>
                        @endauth
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Sin eventos programados.</p>
                @endforelse
            </div>

            {{-- Colectas y Cuotas (Módulo D) --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                <h2 class="font-extrabold text-[#0F2D4A]">Colectas y Cuotas</h2>
                @forelse($collections as $col)
                    <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-2 text-xs">
                        <div class="flex justify-between font-bold">
                            <span class="text-slate-900">{{ $col->title }}</span>
                            <span class="text-[#1D70B8]">{{ $col->progressPercent() }}%</span>
                        </div>
                        <div class="w-full h-2 rounded-full bg-slate-200 overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: {{ $col->progressPercent() }}%"></div>
                        </div>
                        <p class="text-slate-600">Recaudado S/ {{ number_format($col->current_amount, 2) }} de S/ {{ number_format($col->goal_amount, 2) }}</p>
                        @auth
                            <form action="{{ route('communities.collections.contribute', $col->id) }}" method="POST" class="flex gap-2 pt-1">
                                @csrf
                                <input type="number" step="1" min="5" name="amount" value="50" required class="w-20 px-2 py-1 rounded-lg border border-slate-300 bg-white">
                                <input type="text" name="reference_code" placeholder="Cód. Yape" class="flex-1 px-2 py-1 rounded-lg border border-slate-300 bg-white">
                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-[#1D70B8] text-white font-bold">Aportar</button>
                            </form>
                        @endauth
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Sin colectas activas.</p>
                @endforelse

                @auth
                    <form action="{{ route('communities.collections.store', $community->slug) }}" method="POST" class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                        @csrf
                        <p class="font-bold text-slate-700">Nueva colecta solidaria / cuota</p>
                        <input type="text" name="title" required placeholder="Motivo (ej. Cuota almuerzo)" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300">
                        <input type="number" step="1" min="20" name="goal_amount" required placeholder="Meta S/" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300">
                        <button type="submit" class="w-full py-1.5 rounded-lg bg-slate-900 text-white font-bold">Abrir Colecta</button>
                    </form>
                @endauth
            </div>

            {{-- Álbumes Históricos --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                <h2 class="font-extrabold text-[#0F2D4A]">Álbumes Históricos</h2>
                @forelse($albums as $album)
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-slate-800">{{ $album->title }} @if($album->year_label) ({{ $album->year_label }}) @endif</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($album->photos as $photo)
                                <div class="rounded-lg overflow-hidden border border-slate-200 bg-slate-50">
                                    <img src="{{ asset(ltrim($photo->image_path, '/')) }}" alt="{{ $photo->caption }}" class="w-full h-20 object-cover">
                                    <p class="p-1.5 text-[10px] text-slate-600 line-clamp-1">{{ $photo->caption }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500">Sin álbumes históricos aún.</p>
                @endforelse

                @auth
                    <form action="{{ route('communities.albums.store', $community->slug) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-100 space-y-2 text-xs">
                        @csrf
                        <p class="font-bold text-slate-700">Crear álbum histórico</p>
                        <input type="text" name="title" required placeholder="Título del álbum" class="w-full px-2.5 py-1.5 rounded-lg border border-slate-300">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="year_label" placeholder="Año (ej. 2006)" class="px-2.5 py-1.5 rounded-lg border border-slate-300">
                            <input type="text" name="caption" required placeholder="Descripción foto" class="px-2.5 py-1.5 rounded-lg border border-slate-300">
                        </div>
                        <input type="file" name="photo" accept="image/*" class="block w-full">
                        <button type="submit" class="w-full py-1.5 rounded-lg bg-[#1D70B8] text-white font-bold">Publicar Álbum</button>
                    </form>
                @endauth
            </div>
        </aside>
    </div>
</div>
@endsection
