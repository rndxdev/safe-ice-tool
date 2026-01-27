<?php

namespace App\Http\Controllers;

use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentLikeController extends Controller
{
    private const ALLOWED_TYPES = ['feed_comment', 'trip_post_comment'];

    public function toggle(Request $request)
    {
        $data = $request->validate([
            'comment_type' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_TYPES)],
            'comment_id' => ['required', 'integer', 'min:1'],
        ]);

        $existing = CommentLike::query()
            ->where('user_id', Auth::id())
            ->where('comment_type', $data['comment_type'])
            ->where('comment_id', $data['comment_id'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CommentLike::create([
                'user_id' => Auth::id(),
                'comment_type' => $data['comment_type'],
                'comment_id' => $data['comment_id'],
            ]);
        }

        return back();
    }
}
