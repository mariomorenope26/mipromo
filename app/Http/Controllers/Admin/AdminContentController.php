<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Institution;
use App\Models\PromotionalBanner;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminContentController extends Controller
{
    public function index(): View
    {
        $posts = BlogPost::with(['institution', 'author'])->latest()->get();
        $banners = PromotionalBanner::orderBy('sort_order')->latest()->get();
        $institutions = Institution::orderBy('name')->get();

        return view('admin.content.index', compact('posts', 'banners', 'institutions'));
    }

    public function storePost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'category' => ['required', 'in:noticia,aniversario,comunidad,guia'],
            'institution_id' => ['nullable', 'exists:institutions,id'],
            'excerpt' => ['required', 'string', 'max:400'],
            'body' => ['required', 'string'],
        ]);

        $post = BlogPost::create([
            ...$validated,
            'author_id' => $request->user()->id,
            'slug' => Str::slug($validated['title']).'-'.strtolower(Str::random(4)),
            'image_path' => '/images/products/anuario.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        AuditLogger::log('content.post_created', "Artículo publicado: {$post->title}", $post);

        return back()->with('success', "Noticia '{$post->title}' publicada.");
    }

    public function togglePost(BlogPost $post): RedirectResponse
    {
        $post->update(['is_published' => ! $post->is_published]);

        return back()->with('success', 'Estado de publicación actualizado.');
    }

    public function storeBanner(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'subtitle' => ['required', 'string', 'max:255'],
            'badge_text' => ['nullable', 'string', 'max:60'],
            'season' => ['required', 'string', 'max:40'],
            'cta_label' => ['required', 'string', 'max:60'],
            'cta_url' => ['required', 'string', 'max:255'],
            'bg_color' => ['required', 'string', 'max:20'],
        ]);

        PromotionalBanner::create([
            ...$validated,
            'is_active' => true,
            'sort_order' => PromotionalBanner::count() + 1,
        ]);

        return back()->with('success', 'Banner promocional creado.');
    }

    public function toggleBanner(PromotionalBanner $banner): RedirectResponse
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', 'Visibilidad del banner actualizada.');
    }
}
