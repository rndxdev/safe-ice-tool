<?php

namespace App\Services;

use App\Models\Lake;

class LakeSafetyService
{
    public function computeForLake(Lake $lake, int $days = 10): array
    {
        $since = now()->subDays($days);

        // Include hidden reports too so disagreement/trolling shows up in the score
        $reports = $lake->iceReports()
            ->where('created_at', '>=', $since)
            ->get();

        if ($reports->isEmpty()) {
            return [
                'score' => null,
                'label' => 'Not enough data',
                'summary' => 'Not enough recent reports to estimate conditions.',
                'metrics' => [
                    'window_days' => $days,
                    'report_count' => 0,
                ],
            ];
        }

        $total = $reports->count();

        $avgThickness = $reports->avg('thickness_inches');

        $slushCount = $reports->where('has_slush', true)->count();
        $crackCount = $reports->where('has_pressure_cracks', true)->count();

        $hiddenCount = $reports->where('is_hidden', true)->count();
        $downvotes = $reports->sum('downvotes');

        $slushRatio = $total > 0 ? $slushCount / $total : 0;
        $crackRatio = $total > 0 ? $crackCount / $total : 0;
        $hiddenRatio = $total > 0 ? $hiddenCount / $total : 0;
        $downvotesPerReport = $total > 0 ? $downvotes / $total : 0;

        // Start from neutral 50, then nudge up or down
        $score = 50;

        // Thickness effect
        if ($avgThickness !== null) {
            if ($avgThickness >= 8) {
                $score += 45;
            } elseif ($avgThickness >= 4) {
                $score += 20;
            } else {
                $score -= 20;
            }
        }

        // Slush effect
        if ($slushRatio > 0.6) {
            $score -= 20;
        } elseif ($slushRatio > 0.3) {
            $score -= 10;
        }

        // Pressure cracks effect
        if ($crackRatio > 0.5) {
            $score -= 25;
        } elseif ($crackRatio > 0.2) {
            $score -= 10;
        }

        // Hidden/disputed reports
        if ($hiddenRatio > 0.4) {
            $score -= 15;
        } elseif ($hiddenRatio > 0.2) {
            $score -= 5;
        }

        // Downvotes as proxy for disagreement/low quality
        if ($downvotesPerReport > 2) {
            $score -= 10;
        } elseif ($downvotesPerReport > 0.5) {
            $score -= 5;
        }

        // Clamp between 0 and 100
        $score = max(0, min(100, $score));

        if ($score >= 75) {
            $label = 'Stable freeze pattern';
            $summary = 'Recent reports suggest generally consistent ice with relatively few problem indicators.';
        } elseif ($score >= 50) {
            $label = 'Mixed conditions';
            $summary = 'Some parts of the lake may be fine, but reports show enough issues that you should be selective and cautious.';
        } else {
            $label = 'Inconsistent conditions';
            $summary = 'Reports show thin ice, slush, cracks, or disagreement between users. Treat this lake as highly variable.';
        }

        return [
            'score' => $score,
            'label' => $label,
            'summary' => $summary,
            'metrics' => [
                'window_days' => $days,
                'report_count' => $total,
                'avg_thickness' => $avgThickness,
                'slush_ratio' => $slushRatio,
                'crack_ratio' => $crackRatio,
                'hidden_ratio' => $hiddenRatio,
                'downvotes_per_report' => $downvotesPerReport,
            ],
        ];
    }
}
