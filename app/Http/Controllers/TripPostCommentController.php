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
            'parent_id' => ['nullable', 'integer', 'min:1'],
        ]);

        if (!empty($data['parent_id'])) {
            $parent = TripPostComment::query()
                ->where('id', $data['parent_id'])
                ->where('trip_post_id', $post->id)
                ->firstOrFail();
        }

        TripPostComment::create([
            'trip_post_id' => $post->id,
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'Comment posted.');
    }
}
