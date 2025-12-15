<?php

namespace App\Http\Controllers;

use App\Models\IceReport;
use App\Models\Lake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class IceReportController extends Controller
{
    public function store(Request $request, string $slug): RedirectResponse
    {
        $lake = Lake::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'thickness_inches' => ['required', 'numeric', 'min:0', 'max:60'],
            'ice_type' => ['nullable', 'string', 'max:50'],
            'traffic_type' => ['nullable', 'string', 'max:50'],
            'has_slush' => ['sometimes', 'boolean'],
            'has_pressure_cracks' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        IceReport::create([
            'lake_id' => $lake->id,
            'user_id' => Auth::id(),
            'thickness_inches' => $data['thickness_inches'],
            'ice_type' => $data['ice_type'] ?? null,
            'traffic_type' => $data['traffic_type'] ?? null,
            'has_slush' => $data['has_slush'] ?? false,
            'has_pressure_cracks' => $data['has_pressure_cracks'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('lakes.show', $lake->slug)
            ->with('success', 'Report submitted.');
    }

   public function myReports()
{
    $userId = Auth::id();

    $reports = IceReport::with('lake')
        ->where('user_id', $userId)
        ->latest()
        ->get([
            'id',
            'lake_id',
            'thickness_inches',
            'ice_type',
            'traffic_type',
            'has_slush',
            'has_pressure_cracks',
            'upvotes',
            'downvotes',
            'is_hidden',
            'is_flagged',
            'notes',
            'created_at',
        ]);

    return Inertia::render('Reports/MyReports', [
        'reports' => $reports,
    ]);
}


    public function upvote(IceReport $report): RedirectResponse
{
    $report->increment('upvotes');

    $this->maybeModerate($report);

    return back()->with('success', 'Thanks for voting.');
}

public function downvote(IceReport $report): RedirectResponse
{
    $report->increment('downvotes');

    $this->maybeModerate($report);

    return back()->with('success', 'Vote recorded.');
}

protected function maybeModerate(IceReport $report): void
{
    $report->refresh();

    $score = $report->upvotes - $report->downvotes;

    if ($score <= -5) {
        $report->update([
            'is_hidden' => true,
            'is_flagged' => true,
        ]);
    }
}

}
