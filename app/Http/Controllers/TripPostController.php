<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripPost;
use App\Models\TripPostMedia;
use App\Models\TripPostComment;
use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TripPostController extends Controller
{
    public function index()
    {
        $posts = TripPost::query()
            ->with(['user:id,name,username', 'lake:id,name,slug,region', 'media'])
            ->where('is_public', true)
            ->latest()
            ->limit(50)
            ->get();

        $postIds = $posts->pluck('id')->values()->all();

        $comments = TripPostComment::with('user:id,name,username')
            ->whereIn('trip_post_id', $postIds)
            ->latest()
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

        $commentLikedByUser = $commentLikes
            ->where('user_id', Auth::id())
            ->pluck('comment_id')
            ->flip();

        $commentsByPost = $comments
            ->map(function (TripPostComment $comment) use ($commentLikeCounts, $commentLikedByUser) {
                return [
                    'id' => $comment->id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at,
                    'parent_id' => $comment->parent_id,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'username' => $comment->user->username,
                    ] : null,
                    'like_count' => (int) ($commentLikeCounts[$comment->id] ?? 0),
                    'liked' => $commentLikedByUser->has($comment->id),
                    'post_id' => $comment->trip_post_id,
                ];
            })
            ->groupBy('post_id')
            ->map(function ($group) {
                $byId = $group->keyBy('id');
                $children = [];
                foreach ($group as $comment) {
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
                return $byId
                    ->filter(fn ($c) => empty($c['parent_id']))
                    ->sortByDesc('created_at')
                    ->take(2)
                    ->sortBy('created_at')
                    ->values();
            });

        $posts = $posts->map(function (TripPost $post) use ($commentsByPost) {
            $post->comments_preview = $commentsByPost[$post->id] ?? [];
            return $post;
        });

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    public function show(TripPost $post)
{
    $post->load([
        'trip.lake',
        'user',
        'media',
    ]);

    $comments = $post->comments()
        ->with('user')
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

    $commentLikedByUser = $commentLikes
        ->where('user_id', Auth::id())
        ->pluck('comment_id')
        ->flip();

    return Inertia::render('Posts/Show', [
        'post' => [
            'id' => $post->id,
            'caption' => $post->caption,
            'created_at' => $post->created_at,
            'user' => $post->user ? [
                'id' => $post->user->id,
                'name' => $post->user->name,
                'username' => $post->user->username,
            ] : null,
            'lake' => $post->trip && $post->trip->lake ? [
                'name' => $post->trip->lake->name,
                'slug' => $post->trip->lake->slug,
                'region' => $post->trip->lake->region,
            ] : null,
            'media' => $post->media->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'mime' => $m->mime,
            ])->values(),
        ],
        'comments' => $comments->map(fn ($c) => [
            'id' => $c->id,
            'body' => $c->body,
            'created_at' => $c->created_at,
            'parent_id' => $c->parent_id,
            'user' => $c->user ? [
                'id' => $c->user->id,
                'name' => $c->user->name,
                'username' => $c->user->username,
            ] : null,
            'like_count' => (int) ($commentLikeCounts[$c->id] ?? 0),
            'liked' => $commentLikedByUser->has($c->id),
        ])->values(),
    ]);
}


    public function createForTrip(Trip $trip)
    {
        if ((int) $trip->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $trip->load('lake:id,name,slug,region');

        return Inertia::render('Posts/Create', [
            'trip' => $trip,
        ]);
    }

    public function storeForTrip(Request $request, Trip $trip)
    {
        if ((int) $trip->user_id !== (int) Auth::id()) {
            abort(403);
        }

      $data = $request->validate([
    'caption' => ['nullable', 'string', 'max:5000'],
    'people_tags' => ['nullable', 'array'],
    'people_tags.*' => ['string', 'max:50'],
    'location_tags' => ['nullable', 'array'],
    'location_tags.*' => ['string', 'max:50'],
    'is_public' => ['nullable'],
    'photos' => ['nullable', 'array', 'max:10'],
    'photos.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
]);

$post = TripPost::create([
    'user_id' => Auth::id(),
    'trip_id' => $trip->id,
    'lake_id' => $trip->lake_id,
    'caption' => $data['caption'] ?? null,
    'people_tags' => $data['people_tags'] ?? null,
    'location_tags' => $data['location_tags'] ?? null,
    'is_public' => $request->boolean('is_public', true),
]);


        $files = $request->file('photos', []);
        $i = 0;

        foreach ($files as $file) {
            $path = $file->store('posts', 'public');

            TripPostMedia::create([
                'trip_post_id' => $post->id,
                'path' => $path,
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
                'sort_order' => $i++,
            ]);
        }

        return redirect()->route('posts.show', $post->id);
    }
}
