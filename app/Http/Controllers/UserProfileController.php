<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserProfileController extends Controller
{
    /**
     * Get public profile data for a user by username.
     */
    public function show(string $username): JsonResponse
    {
        $user = User::where('username', $username)->firstOrFail();

        $visibility = $user->profile_visibility ?? [];

        // Always visible
        $profile = [
            'id' => $user->id,
            'username' => $user->username,
            'bio' => $user->bio,
            'member_since' => $user->created_at?->format('F Y'),
        ];

        // Conditionally visible fields
        if ($visibility['show_name'] ?? true) {
            $profile['name'] = $user->name;
        }

        if ($visibility['show_location'] ?? false) {
            $profile['location'] = $user->location;
        }

        // Stats (always public)
        $profile['stats'] = [
            'reports_count' => $user->iceReports()->count(),
            'lakes_count' => $user->favoriteLakes()->count(),
        ];

        return response()->json($profile);
    }
}
