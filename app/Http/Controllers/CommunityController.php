<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\CommunityAlbum;
use App\Models\CommunityAlbumPhoto;
use App\Models\CommunityCollection;
use App\Models\CommunityCollectionContribution;
use App\Models\CommunityComment;
use App\Models\CommunityEvent;
use App\Models\CommunityEventRsvp;
use App\Models\CommunityMember;
use App\Models\CommunityPoll;
use App\Models\CommunityPollOption;
use App\Models\CommunityPollVote;
use App\Models\CommunityPost;
use App\Models\ContentReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(Request $request): View
    {
        $query = Community::query()
            ->with(['institution', 'promotion', 'classroom'])
            ->withCount(['members', 'posts', 'events']);

        if ($type = $request->string('tipo')->toString()) {
            $query->where('type', $type);
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where('name', 'like', "%{$search}%");
        }

        $communities = $query->orderBy('type')->orderBy('name')->paginate(12)->withQueryString();

        return view('communities.index', compact('communities'));
    }

    public function show(Community $community): View
    {
        $community->load(['institution', 'promotion', 'classroom'])
            ->loadCount(['members', 'posts', 'events']);

        $posts = $community->posts()
            ->with(['user', 'comments.user'])
            ->where('status', 'published')
            ->orderByDesc('is_pinned')
            ->latest()
            ->get();

        $events = $community->events()
            ->with('rsvps')
            ->get();

        $polls = $community->polls()
            ->with(['options', 'votes'])
            ->get();

        $collections = $community->collections()
            ->with('contributions')
            ->get();

        $albums = $community->albums()
            ->with('photos')
            ->get();

        $isMember = auth()->check()
            ? CommunityMember::where('community_id', $community->id)->where('user_id', auth()->id())->exists()
            : false;

        return view('communities.show', compact('community', 'posts', 'events', 'polls', 'collections', 'albums', 'isMember'));
    }

    public function join(Request $request, Community $community): RedirectResponse
    {
        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($member) {
            $member->delete();

            return back()->with('success', "Saliste de la comunidad {$community->name}.");
        }

        CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'member_role' => 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', "¡Te uniste a {$community->name}!");
    }

    public function storePost(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'in:general,anuncio,recuerdo,campana,colecta'],
            'title' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'min:4', 'max:3000'],
        ]);

        CommunityMember::firstOrCreate(
            ['community_id' => $community->id, 'user_id' => $request->user()->id],
            ['member_role' => 'member', 'joined_at' => now()]
        );

        CommunityPost::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'category' => $validated['category'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'status' => 'published',
        ]);

        return back()->with('success', 'Publicación compartida en el muro.');
    }

    public function likePost(CommunityPost $post): RedirectResponse
    {
        $post->increment('likes_count');

        return back();
    }

    public function storeComment(Request $request, CommunityPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ]);

        CommunityComment::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function storeEvent(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1500'],
            'location' => ['required', 'string', 'max:200'],
            'starts_at' => ['required', 'date'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        CommunityEvent::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'],
            'starts_at' => $validated['starts_at'],
            'ticket_price' => $validated['ticket_price'] ?? 0,
        ]);

        return back()->with('success', 'Evento creado correctamente.');
    }

    public function rsvpEvent(Request $request, CommunityEvent $event): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:going,maybe,not_going'],
        ]);

        CommunityEventRsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            ['status' => $validated['status']]
        );

        return back()->with('success', 'Confirmación de asistencia registrada.');
    }

    public function votePoll(Request $request, CommunityPoll $poll): RedirectResponse
    {
        $validated = $request->validate([
            'option_id' => ['required', 'exists:community_poll_options,id'],
        ]);

        $option = CommunityPollOption::where('poll_id', $poll->id)->findOrFail($validated['option_id']);

        $alreadyVoted = CommunityPollVote::where('poll_id', $poll->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($alreadyVoted) {
            return back()->with('error', 'Ya emitiste tu voto en esta encuesta.');
        }

        DB::transaction(function () use ($poll, $option, $request): void {
            CommunityPollVote::create([
                'poll_id' => $poll->id,
                'option_id' => $option->id,
                'user_id' => $request->user()->id,
            ]);
            $option->increment('votes_count');
        });

        return back()->with('success', '¡Voto registrado!');
    }

    public function reportPost(Request $request, CommunityPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:120'],
            'details' => ['nullable', 'string', 'max:500'],
        ]);

        ContentReport::create([
            'user_id' => $request->user()->id,
            'reportable_type' => CommunityPost::class,
            'reportable_id' => $post->id,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'Reporte enviado al equipo de moderación.');
    }

    public function storeCollection(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'goal_amount' => ['required', 'numeric', 'min:20'],
            'ends_at' => ['nullable', 'date'],
        ]);

        CommunityCollection::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'goal_amount' => $validated['goal_amount'],
            'ends_at' => $validated['ends_at'] ?? now()->addDays(30),
            'status' => 'active',
        ]);

        return back()->with('success', 'Colecta / cuota solidaria creada en la comunidad.');
    }

    public function contributeCollection(Request $request, CommunityCollection $collection): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:5'],
            'reference_code' => ['nullable', 'string', 'max:80'],
        ]);

        DB::transaction(function () use ($collection, $request, $validated): void {
            CommunityCollectionContribution::create([
                'collection_id' => $collection->id,
                'user_id' => $request->user()->id,
                'contributor_name' => $request->user()->name,
                'amount' => $validated['amount'],
                'reference_code' => $validated['reference_code'] ?? null,
            ]);

            $collection->increment('current_amount', $validated['amount']);
        });

        return back()->with('success', '¡Gracias por tu aporte a la colecta!');
    }

    public function storeAlbum(Request $request, Community $community): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'year_label' => ['nullable', 'string', 'max:40'],
            'caption' => ['required', 'string', 'max:200'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $imagePath = '/images/products/anuario.jpg';
        if ($request->hasFile('photo')) {
            $imagePath = '/storage/'.$request->file('photo')->store('albums', 'public');
        }

        $album = CommunityAlbum::create([
            'community_id' => $community->id,
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'year_label' => $validated['year_label'] ?? null,
        ]);

        CommunityAlbumPhoto::create([
            'album_id' => $album->id,
            'user_id' => $request->user()->id,
            'caption' => $validated['caption'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Álbum histórico agregado a la comunidad.');
    }
}
