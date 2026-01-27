<?php

namespace App\Http\Controllers;

use App\Models\TripPost;
use App\Models\TripPostComment;
use App\Models\CommentLike;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TripPostShareController extends Controller
{
    public function show(string $token)
    {
        $post = TripPost::query()
            ->with(['user:id,name', 'lake:id,name,slug,region', 'media'])
            ->whereNotNull('share_token')
            ->where('share_token', $token)
            ->firstOrFail();

        if (!$post->is_public) {
            abort(404);
        }

        $comments = TripPostComment::with('user')
            ->where('trip_post_id', $post->id)
            ->orderBy('created_at', 'asc')
            ->get([
                'id',
                'trip_post_id',
                'user_id',
                'parent_id',
                'body',
                'created_at',
            ]);

        $commentIds = $comments->pluck('id')->values()->all();
        $commentLikes = CommentLike::query()
            ->where('comment_type', 'trip_post_comment')
            ->whereIn('comment_id', $commentIds)
            ->get(['comment_id', 'user_id']);

        $commentLikeCounts = $commentLikes
            ->groupBy('comment_id')
            ->map(fn ($group) => $group->count());

        $commentTree = $comments->map(fn ($c) => [
            'id' => $c->id,
            'body' => $c->body,
            'created_at' => $c->created_at,
            'parent_id' => $c->parent_id,
            'user' => $c->user ? [
                'id' => $c->user->id,
                'name' => $c->user->name,
            ] : null,
            'like_count' => (int) ($commentLikeCounts[$c->id] ?? 0),
            'liked' => false,
        ])->values();

        $byId = $commentTree->keyBy('id');
        $children = [];
        foreach ($commentTree as $comment) {
            if (!empty($comment['parent_id'])) {
                $children[$comment['parent_id']][] = $comment;
            }
        }
        foreach ($children as $parentId => $replies) {
            if ($byId->has($parentId)) {
                $parent = $byId[$parentId];
                $parent['replies'] = collect($replies)->sortBy('created_at')->values();
                $byId[$parentId] = $parent;
            }
        }
        $thread = $byId
            ->filter(fn ($c) => empty($c['parent_id']))
            ->sortBy('created_at')
            ->values();

        return Inertia::render('Posts/Share', [
            'post' => $post,
            'comments' => $thread,
            'shareUrl' => url()->current(),
            'canLogin' => route_has('login'),
            'canRegister' => route_has('register'),
        ]);
    }

    public function share(TripPost $post): RedirectResponse
    {
        if ((int) $post->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (empty($post->share_token)) {
            $post->share_token = Str::random(32);
        }

        $post->is_public = true;
        $post->save();

        return back()->with('share_url', url('/p/' . $post->share_token));
    }

    public function unshare(TripPost $post): RedirectResponse
    {
        if ((int) $post->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $post->share_token = null;
        $post->save();

        return back()->with('success', 'Post link removed.');
    }
}

function route_has(string $name): bool
{
    return \Illuminate\Support\Facades\Route::has($name);
}
