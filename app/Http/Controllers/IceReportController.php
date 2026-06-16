<?php

namespace App\Http\Controllers;

use App\Models\IceReport;
use App\Models\IceReportVote;
use App\Models\Lake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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
        $this->castVote($report, IceReportVote::UP);

        return back()->with('success', 'Thanks for voting.');
    }

    public function downvote(IceReport $report): RedirectResponse
    {
        $this->castVote($report, IceReportVote::DOWN);

        return back()->with('success', 'Vote recorded.');
    }

    /**
     * Record one vote per user. Pressing the same direction again clears the
     * vote (toggle off); the opposite direction switches it. Counts are
     * recomputed from the votes table so they can't be inflated by spamming.
     */
    protected function castVote(IceReport $report, int $value): void
    {
        $userId = Auth::id();

        DB::transaction(function () use ($report, $userId, $value) {
            $existing = IceReportVote::where('ice_report_id', $report->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->value === $value) {
                $existing->delete();
            } else {
                IceReportVote::updateOrCreate(
                    ['ice_report_id' => $report->id, 'user_id' => $userId],
                    ['value' => $value],
                );
            }

            $this->syncVoteCounts($report);
        });
    }

    /**
     * Recompute the denormalised counters from the votes table and apply the
     * -5 net-score auto-hide. Hiding is one-way (a recovering report stays
     * hidden until a moderator reviews it), preserving prior behaviour.
     */
    protected function syncVoteCounts(IceReport $report): void
    {
        $upvotes = $report->votes()->where('value', IceReportVote::UP)->count();
        $downvotes = $report->votes()->where('value', IceReportVote::DOWN)->count();

        $report->upvotes = $upvotes;
        $report->downvotes = $downvotes;

        if (! $report->is_hidden && ($upvotes - $downvotes) <= -5) {
            $report->is_hidden = true;
            $report->is_flagged = true;
        }

        $report->save();
    }
}
