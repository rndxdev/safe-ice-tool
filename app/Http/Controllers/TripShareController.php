<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TripShareController extends Controller
{
    private function assertOwner(Trip $trip): void
    {
        $userId = Auth::id();
        if (!$userId || (int) $trip->user_id !== (int) $userId) {
            abort(403);
        }
    }

    public function createShareLink(Trip $trip): JsonResponse
    {
        $this->assertOwner($trip);

        // Reuse if already shared
        if ($trip->is_public && !empty($trip->share_token)) {
            return response()->json([
                'shareUrl' => url('/t/' . $trip->share_token),
            ]);
        }

        $trip->share_token = Str::random(32);
        $trip->is_public = true;
        $trip->save();

        return response()->json([
            'shareUrl' => url('/t/' . $trip->share_token),
        ]);
    }

    public function share(Trip $trip): RedirectResponse
    {
        $this->assertOwner($trip);

        if (!$trip->is_public || empty($trip->share_token)) {
            $trip->share_token = Str::random(32);
            $trip->is_public = true;
            $trip->save();
        }

        return back()->with('share_url', url('/t/' . $trip->share_token));
    }

    public function unshare(Trip $trip): RedirectResponse
    {
        $this->assertOwner($trip);

        $trip->is_public = false;
        $trip->share_token = null;
        $trip->save();

        return back()->with('success', 'Trip is no longer shared.');
    }

    public function rotate(Trip $trip): RedirectResponse
    {
        $this->assertOwner($trip);

        $trip->share_token = Str::random(32);
        $trip->is_public = true;
        $trip->save();

        return back()->with('share_url', url('/t/' . $trip->share_token));
    }
}
