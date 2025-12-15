<?php

namespace App\Http\Controllers;

use App\Models\TripPost;
use App\Models\TripPostComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TripPostCommentController extends Controller
{
    public function store(Request $request, TripPost $post)
    {
        if (!$post->is_public && (int) $post->user_id !== (int) Auth::id()) {
            abort(404);
        }

        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        TripPostComment::create([
            'trip_post_id' => $post->id,
            'user_id' => Auth::id(),
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comment posted.');
    }
}
