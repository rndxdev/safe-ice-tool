<?php

namespace App\Http\Controllers;

use App\Models\IceReport;
use App\Models\Lake;
use App\Models\LakeVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LakeVerificationController extends Controller
{
    private const VERDICTS = ['approve', 'reject', 'flag'];
    private const APPROVAL_THRESHOLD = 2;
    private const REJECT_THRESHOLD = 2;
    private const FLAG_THRESHOLD = 2;

    public function store(Request $request, Lake $lake)
    {
        $data = $request->validate([
            'verdict' => ['required', 'string', 'in:' . implode(',', self::VERDICTS)],
        ]);

        if ((int) $lake->created_by_user_id === (int) Auth::id()) {
            abort(403);
        }

        if ($lake->status !== 'pending') {
            return back();
        }

        LakeVerification::updateOrCreate([
            'lake_id' => $lake->id,
            'user_id' => Auth::id(),
        ], [
            'verdict' => $data['verdict'],
        ]);

        $verifications = LakeVerification::where('lake_id', $lake->id)->get(['verdict']);
        $approvals = $verifications->where('verdict', 'approve')->count();
        $rejects = $verifications->where('verdict', 'reject')->count();
        $flags = $verifications->where('verdict', 'flag')->count();

        $reportCount = IceReport::where('lake_id', $lake->id)
            ->where('is_hidden', false)
            ->count();

        if ($rejects >= self::REJECT_THRESHOLD || $flags >= self::FLAG_THRESHOLD) {
            $lake->status = 'rejected';
            $lake->is_active = false;
            $lake->save();
            return back()->with('success', 'Lake rejected.');
        }

        if ($reportCount >= 1 && $approvals >= self::APPROVAL_THRESHOLD && $flags === 0 && $rejects === 0) {
            $lake->status = 'approved';
            $lake->is_active = true;
            $lake->save();
            return back()->with('success', 'Lake approved.');
        }

        return back()->with('success', 'Verification recorded.');
    }
}
