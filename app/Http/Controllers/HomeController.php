<?php

namespace App\Http\Controllers;

use App\Models\AlumniProfile;
use App\Models\BlogPost;
use App\Models\Campaign;
use App\Models\Community;
use App\Models\Institution;
use App\Models\PromotionalBanner;
use App\Services\StorefrontService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(private readonly StorefrontService $storefront) {}

    public function index(Request $request): View
    {
        $products = $this->storefront->filterProducts($request, 12);
        $categories = $this->storefront->categoriesWithCount();
        $bundles = $this->storefront->suggestedBundles();

        $institutions = Institution::published()
            ->withCount(['promotions', 'alumniProfiles', 'activeDelegates'])
            ->orderBy('city')
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->get();

        $cities = $institutions->pluck('city')->unique()->values();

        $campaigns = Campaign::query()
            ->with(['institution', 'promotion', 'product'])
            ->whereIn('status', ['active', 'goal_reached'])
            ->orderBy('ends_at')
            ->take(3)
            ->get();

        $banners = PromotionalBanner::where('is_active', true)->orderBy('sort_order')->take(2)->get();
        $blogPosts = BlogPost::with('institution')->where('is_published', true)->latest('published_at')->take(3)->get();

        $stats = [
            'institutions' => $institutions->count(),
            'alumni_verified' => AlumniProfile::where('verification_status', 'verified')->count(),
            'active_campaigns' => Campaign::whereIn('status', ['active', 'goal_reached'])->count(),
            'communities' => Community::count(),
        ];

        return view('home', compact(
            'products',
            'categories',
            'bundles',
            'institutions',
            'cities',
            'campaigns',
            'banners',
            'blogPosts',
            'stats'
        ));
    }

    public function howItWorks(): View
    {
        $institutions = Institution::published()->orderBy('name')->get();
        $bundles = $this->storefront->suggestedBundles();
        $stats = [
            'institutions' => $institutions->count(),
            'alumni_verified' => AlumniProfile::where('verification_status', 'verified')->count(),
            'active_campaigns' => Campaign::whereIn('status', ['active', 'goal_reached'])->count(),
        ];

        return view('pages.how-it-works', compact('institutions', 'bundles', 'stats'));
    }
}
