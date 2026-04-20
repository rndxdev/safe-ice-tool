<?php

namespace App\Services;

use App\Models\Lake;
use Illuminate\Support\Collection;

class LakeSafetyService
{
    public function computeForLake(Lake $lake, int $days = 10): array
    {
        $reports = $lake->iceReports()
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        if ($reports->isEmpty()) {
            return $this->emptyResult($days);
        }

        $metrics = $this->computeMetrics($reports, $days);
        $score = $this->computeScore($metrics);
        [$label, $summary] = $this->labelFor($score);

        return [
            'score' => $score,
            'label' => $label,
            'summary' => $summary,
            'metrics' => $metrics,
        ];
    }

    private function emptyResult(int $days): array
    {
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

    private function computeMetrics(Collection $reports, int $days): array
    {
        $total = $reports->count();

        return [
            'window_days' => $days,
            'report_count' => $total,
            'avg_thickness' => $reports->avg('thickness_inches'),
            'slush_ratio' => $reports->where('has_slush', true)->count() / $total,
            'crack_ratio' => $reports->where('has_pressure_cracks', true)->count() / $total,
            'hidden_ratio' => $reports->where('is_hidden', true)->count() / $total,
            'downvotes_per_report' => $reports->sum('downvotes') / $total,
        ];
    }

    private function computeScore(array $metrics): int
    {
        $score = 50
            + $this->thicknessAdjustment($metrics['avg_thickness'])
            + $this->slushAdjustment($metrics['slush_ratio'])
            + $this->crackAdjustment($metrics['crack_ratio'])
            + $this->hiddenAdjustment($metrics['hidden_ratio'])
            + $this->downvoteAdjustment($metrics['downvotes_per_report']);

        return max(0, min(100, $score));
    }

    private function thicknessAdjustment(?float $avg): int
    {
        if ($avg === null) {
            return 0;
        }
        if ($avg >= 8) {
            return 45;
        }
        if ($avg >= 4) {
            return 20;
        }
        return -20;
    }

    private function slushAdjustment(float $ratio): int
    {
        if ($ratio > 0.6) {
            return -20;
        }
        if ($ratio > 0.3) {
            return -10;
        }
        return 0;
    }

    private function crackAdjustment(float $ratio): int
    {
        if ($ratio > 0.5) {
            return -25;
        }
        if ($ratio > 0.2) {
            return -10;
        }
        return 0;
    }

    private function hiddenAdjustment(float $ratio): int
    {
        if ($ratio > 0.4) {
            return -15;
        }
        if ($ratio > 0.2) {
            return -5;
        }
        return 0;
    }

    private function downvoteAdjustment(float $perReport): int
    {
        if ($perReport > 2) {
            return -10;
        }
        if ($perReport > 0.5) {
            return -5;
        }
        return 0;
    }

    private function labelFor(int $score): array
    {
        if ($score >= 75) {
            return [
                'Stable freeze pattern',
                'Recent reports suggest generally consistent ice with relatively few problem indicators.',
            ];
        }
        if ($score >= 50) {
            return [
                'Mixed conditions',
                'Some parts of the lake may be fine, but reports show enough issues that you should be selective and cautious.',
            ];
        }
        return [
            'Inconsistent conditions',
            'Reports show thin ice, slush, cracks, or disagreement between users. Treat this lake as highly variable.',
        ];
    }
}
