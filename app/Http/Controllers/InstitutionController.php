<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Institution::published()
            ->withCount(['promotions', 'alumniProfiles', 'campaigns', 'communities', 'activeDelegates']);

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%");
            });
        }

        if ($city = $request->string('ciudad')->toString()) {
            $query->where('city', $city);
        }

        if ($region = $request->string('region')->toString()) {
            $query->where('region', $region);
        }

        $institutions = $query->orderBy('city')->orderBy('name')->paginate(12)->withQueryString();
        $cities = Institution::published()->distinct()->pluck('city')->sort();
        $regions = Institution::published()->distinct()->pluck('region')->sort();

        return view('institutions.index', compact('institutions', 'cities', 'regions'));
    }

    public function show(Institution $institution): View
    {
        abort_unless(
            $institution->is_active && ($institution->is_published || auth()->user()?->canAccessAdmin()),
            404
        );

        $institution->load([
            'promotions' => fn ($q) => $q->withCount('alumniProfiles')->with('classrooms'),
            'communities' => fn ($q) => $q->withCount(['members', 'posts']),
            'activeDelegates',
        ]);

        $products = Product::query()
            ->with(['category', 'vendor', 'variants'])
            ->where('is_active', true)
            ->where(function ($q) use ($institution) {
                $q->where('institution_id', $institution->id)
                    ->orWhereNull('institution_id');
            })
            ->orderByRaw('CASE WHEN institution_id = ? THEN 0 ELSE 1 END', [$institution->id])
            ->get();

        $campaigns = $institution->campaigns()
            ->with(['promotion', 'product'])
            ->whereIn('status', ['active', 'goal_reached', 'in_production'])
            ->latest()
            ->get();

        return view('institutions.show', compact('institution', 'products', 'campaigns'));
    }
}
