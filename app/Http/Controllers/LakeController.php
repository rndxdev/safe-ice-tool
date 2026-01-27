<?php

namespace App\Http\Controllers;

use App\Models\Lake;
use App\Services\LakeSafetyService;
use App\Services\ReverseGeoCodeService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\IceReport;
use App\Models\LakeVerification;



class LakeController extends Controller
{
    protected LakeSafetyService $safetyService;

    public function __construct(LakeSafetyService $safetyService)
    {
        $this->safetyService = $safetyService;
    }


    public function create()
    {
        return Inertia::render('Lakes/Create');
    }

    public function store(Request $request, ReverseGeoCodeService $geo)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $name = trim($data['name']);
        $lat = (float) $data['lat'];
        $lng = (float) $data['lng'];

        // Server-side reverse geocode (US-only example)
        $place = $geo->usCensus($lat, $lng); // returns ['state' => ..., 'county' => ...] or null

        $state = $place['state'] ?? null;
        $county = $place['county'] ?? null;

        $slugBase = Str::slug($name);
        $slug = $slugBase;
        $i = 2;

        while (Lake::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i;
            $i++;
        }

        $lake = Lake::create([
            'name' => $name,
            'slug' => $slug,
            'lat' => $lat,
            'lng' => $lng,
            'region' => $state && $county ? ($county . ', ' . $state) : null,
            'state' => $state,
            'county' => $county,
            'is_active' => true,
            'status' => 'pending',
            'created_by_user_id' => Auth::id(),
        ]);


        return redirect()->route('lakes.show', $lake->slug)
            ->with('success', 'Lake added.');
    }



    public function index()
    {
        $user = Auth::user();

        $lakes = Lake::where('is_active', true)
            ->where('status', 'approved')
            ->orderBy('state')
            ->orderBy('county')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'region',
                'lat',
                'lng',
                'state',
                'county',
            ]);

        $favoriteIds = [];

        if ($user) {
            $favoriteIds = $user
                ->favoriteLakes()
                ->pluck('lakes.id')
                ->toArray();
        }

        // decorate with favorite + safety
        $lakes = $lakes->map(function (Lake $lake) use ($favoriteIds) {
            $lake->is_favorite = in_array($lake->id, $favoriteIds, true);
            $lake->safety = $this->safetyService->computeForLake($lake);

            return $lake;
        });

        // group into state -> county -> lakes
        $states = $lakes
            ->groupBy('state')
            ->map(function ($stateGroup, $stateName) {
                $counties = $stateGroup
                    ->groupBy('county')
                    ->map(function ($countyGroup, $countyName) {
                        return [
                            'name' => $countyName,
                            'lakes' => $countyGroup->values(), // keep as array of lakes
                        ];
                    })
                    ->values();

                return [
                    'name' => $stateName,
                    'counties' => $counties,
                ];
            })
            ->values();

        return Inertia::render('Lakes/Index', [
            'states' => $states,
        ]);
    }


    public function show(string $slug)
    {
        $lake = Lake::where('slug', $slug)->firstOrFail();

        if (trim((string) $lake->state) === '' || trim((string) $lake->county) === '') {
            $geo = app(ReverseGeoCodeService::class)
                ->usCensus((float) $lake->lat, (float) $lake->lng);

            if ($geo) {
                $lake->state = $geo['state'];
                $lake->county = $geo['county'];
                $lake->save();
            }
        }

        $reports = $lake->iceReports()
            ->where('is_hidden', false)
            ->latest()
            ->take(50)
            ->get([
                'id',
                'lat',
                'lng',
                'thickness_inches',
                'ice_type',
                'traffic_type',
                'has_slush',
                'has_pressure_cracks',
                'notes',
                'upvotes',
                'downvotes',
                'created_at',
            ]);

        $safety = $this->safetyService->computeForLake($lake);

        return Inertia::render('Lakes/Show', [
            'lake' => $lake,
            'reports' => $reports,
            'safety' => $safety,
        ]);
    }

    public function toggleFavorite(string $slug)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $lake = Lake::where('slug', $slug)->firstOrFail();

        $alreadyFavorite = $user
            ->favoriteLakes()
            ->where('lake_id', $lake->id)
            ->exists();

        if ($alreadyFavorite) {
            $user->favoriteLakes()->detach($lake->id);
        } else {
            $user->favoriteLakes()->attach($lake->id);
        }

        return back()->with('success', 'Favorites updated.');
    }

    public function mine()
    {
        $userId = Auth::id();

        $lakes = Lake::where('created_by_user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'slug', 'region', 'lat', 'lng', 'state', 'county', 'status', 'created_at']);

        $reviewLakes = Lake::where('status', 'pending')
            ->where('created_by_user_id', '!=', $userId)
            ->orderByDesc('created_at')
            ->limit(25)
            ->get(['id', 'name', 'slug', 'region', 'lat', 'lng', 'state', 'county', 'status', 'created_at']);

        $reviewIds = $reviewLakes->pluck('id')->values()->all();
        $reviewReports = IceReport::whereIn('lake_id', $reviewIds)
            ->where('is_hidden', false)
            ->get(['lake_id']);

        $reviewVerifications = LakeVerification::whereIn('lake_id', $reviewIds)
            ->get(['lake_id', 'user_id', 'verdict']);

        $userVerifications = $reviewVerifications
            ->where('user_id', $userId)
            ->keyBy('lake_id');

        $verificationCounts = $reviewVerifications
            ->groupBy('lake_id')
            ->map(function ($group) {
                return [
                    'approve' => $group->where('verdict', 'approve')->count(),
                    'reject' => $group->where('verdict', 'reject')->count(),
                    'flag' => $group->where('verdict', 'flag')->count(),
                ];
            });

        $reportCounts = $reviewReports
            ->groupBy('lake_id')
            ->map(fn ($group) => $group->count());

        $reviewLakes = $reviewLakes->map(function (Lake $lake) use ($verificationCounts, $reportCounts, $userVerifications) {
            $counts = $verificationCounts[$lake->id] ?? ['approve' => 0, 'reject' => 0, 'flag' => 0];
            $lake->verification_counts = $counts;
            $lake->report_count = (int) ($reportCounts[$lake->id] ?? 0);
            $lake->user_verdict = $userVerifications->get($lake->id)?->verdict;
            return $lake;
        });

        return Inertia::render('Lakes/Mine', [
            'lakes' => $lakes,
            'reviewLakes' => $reviewLakes,
        ]);
    }

}
