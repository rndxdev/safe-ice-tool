<?php

namespace App\Http\Controllers;

use App\Models\FeedAcknowledgement;
use App\Models\FeedComment;
use App\Models\FeedReaction;
use App\Models\CommentLike;
use App\Models\IceReport;
use App\Models\Lake;
use App\Models\Trip;
use App\Models\TripPost;
use App\Models\TripPostComment;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $favoriteLakes = $user->favoriteLakes()
            ->select('lakes.id', 'lakes.name', 'lakes.slug', 'lakes.region')
            ->orderBy('lakes.name')
            ->get();

        $favoriteLakeIds = $favoriteLakes->pluck('id')->values()->all();

        $upcomingTrips = Trip::with('lake')
            ->where('user_id', $user->id)
            ->orderBy('trip_date')
            ->limit(5)
            ->get();

        $recentReports = [];

        if (!empty($favoriteLakeIds)) {
            $recentReports = IceReport::with('lake')
                ->whereIn('lake_id', $favoriteLakeIds)
                ->where('is_hidden', false)
                ->latest()
                ->limit(5)
                ->get([
                    'id',
                    'lake_id',
                    'thickness_inches',
                    'ice_type',
                    'traffic_type',
                    'has_slush',
                    'has_pressure_cracks',
                    'notes',
                    'created_at',
                ]);
        }

        $communityFeed = collect();

        $communityFeed = $communityFeed->merge(
            IceReport::with(['lake:id,name,slug,region', 'user:id,name'])
                ->where('is_hidden', false)
                ->latest()
                ->limit(8)
                ->get([
                    'id',
                    'lake_id',
                    'user_id',
                    'thickness_inches',
                    'ice_type',
                    'created_at',
                ])
                ->map(function (IceReport $report) {
                    $lakeName = $report->lake?->name ?? 'a lake';
                    $userName = $report->user?->name ?? 'Someone';

                    return [
                        'type' => 'report',
                        'id' => $report->id,
                        'created_at' => $report->created_at,
                        'title' => 'New ice report',
                        'message' => sprintf('%s reported %s" on %s', $userName, $report->thickness_inches, $lakeName),
                        'lake' => $report->lake ? [
                            'name' => $report->lake->name,
                            'slug' => $report->lake->slug,
                            'region' => $report->lake->region,
                        ] : null,
                        'user' => $report->user ? [
                            'id' => $report->user->id,
                            'name' => $report->user->name,
                        ] : null,
                        'url' => $report->lake ? route('lakes.show', $report->lake->slug) : null,
                    ];
                })
        );

        $communityFeed = $communityFeed->merge(
            TripPost::with(['user:id,name', 'lake:id,name,slug,region'])
                ->where('is_public', true)
                ->latest()
                ->limit(8)
                ->get([
                    'id',
                    'user_id',
                    'lake_id',
                    'caption',
                    'created_at',
                ])
                ->map(function (TripPost $post) {
                    $lakeName = $post->lake?->name ?? 'a lake';
                    $userName = $post->user?->name ?? 'Someone';

                    return [
                        'type' => 'post',
                        'id' => $post->id,
                        'created_at' => $post->created_at,
                        'title' => 'Trip post',
                        'message' => sprintf('%s shared a trip update at %s', $userName, $lakeName),
                        'lake' => $post->lake ? [
                            'name' => $post->lake->name,
                            'slug' => $post->lake->slug,
                            'region' => $post->lake->region,
                        ] : null,
                        'user' => $post->user ? [
                            'id' => $post->user->id,
                            'name' => $post->user->name,
                        ] : null,
                        'url' => route('posts.show', $post->id),
                    ];
                })
        );

        $communityFeed = $communityFeed->merge(
            TripPostComment::with(['user:id,name', 'post:id,lake_id,is_public', 'post.lake:id,name,slug,region'])
                ->whereHas('post', function ($query) {
                    $query->where('is_public', true);
                })
                ->latest()
                ->limit(8)
                ->get([
                    'id',
                    'trip_post_id',
                    'user_id',
                    'body',
                    'created_at',
                ])
                ->map(function (TripPostComment $comment) {
                    $lakeName = $comment->post?->lake?->name ?? 'a lake';
                    $userName = $comment->user?->name ?? 'Someone';

                    return [
                        'type' => 'comment',
                        'id' => $comment->id,
                        'created_at' => $comment->created_at,
                        'title' => 'Comment',
                        'message' => sprintf('%s commented on a trip post at %s', $userName, $lakeName),
                        'lake' => $comment->post && $comment->post->lake ? [
                            'name' => $comment->post->lake->name,
                            'slug' => $comment->post->lake->slug,
                            'region' => $comment->post->lake->region,
                        ] : null,
                        'user' => $comment->user ? [
                            'id' => $comment->user->id,
                            'name' => $comment->user->name,
                        ] : null,
                        'url' => route('posts.show', $comment->trip_post_id),
                    ];
                })
        );

        $communityFeed = $communityFeed->merge(
            Trip::with(['lake:id,name,slug,region', 'user:id,name'])
                ->where('is_public', true)
                ->whereNotNull('share_token')
                ->latest()
                ->limit(8)
                ->get([
                    'id',
                    'user_id',
                    'lake_id',
                    'share_token',
                    'created_at',
                ])
                ->map(function (Trip $trip) {
                    $lakeName = $trip->lake?->name ?? 'a lake';
                    $userName = $trip->user?->name ?? 'Someone';

                    return [
                        'type' => 'trip_share',
                        'id' => $trip->id,
                        'created_at' => $trip->created_at,
                        'title' => 'Trip shared',
                        'message' => sprintf('%s shared a trip to %s', $userName, $lakeName),
                        'lake' => $trip->lake ? [
                            'name' => $trip->lake->name,
                            'slug' => $trip->lake->slug,
                            'region' => $trip->lake->region,
                        ] : null,
                        'user' => $trip->user ? [
                            'id' => $trip->user->id,
                            'name' => $trip->user->name,
                        ] : null,
                        'url' => route('trips.share.show', $trip->share_token),
                    ];
                })
        );

        $communityFeed = $communityFeed->merge(
            Lake::query()
                ->where('is_active', true)
                ->where('status', 'approved')
                ->latest()
                ->limit(6)
                ->get([
                    'id',
                    'name',
                    'slug',
                    'region',
                    'created_at',
                ])
                ->map(function (Lake $lake) {
                    return [
                        'type' => 'lake',
                        'id' => $lake->id,
                        'created_at' => $lake->created_at,
                        'title' => 'New lake added',
                        'message' => sprintf('New lake added: %s', $lake->name),
                        'lake' => [
                            'name' => $lake->name,
                            'slug' => $lake->slug,
                            'region' => $lake->region,
                        ],
                        'user' => null,
                        'url' => route('lakes.show', $lake->slug),
                    ];
                })
        );

        $communityFeed = $communityFeed
            ->sortByDesc('created_at')
            ->take(12)
            ->values();

        $userId = $user->id;
        $idsByType = $communityFeed->groupBy('type')->map(fn ($items) => $items->pluck('id')->values()->all());

        $acknowledged = FeedAcknowledgement::query()
            ->where('user_id', $userId)
            ->whereIn('item_type', $idsByType->keys())
            ->whereIn('item_id', $idsByType->flatten()->values()->all())
            ->get(['item_type', 'item_id'])
            ->map(fn ($row) => $row->item_type . ':' . $row->item_id)
            ->flip();

        $liked = FeedReaction::query()
            ->where('user_id', $userId)
            ->where('reaction', 'like')
            ->whereIn('item_type', $idsByType->keys())
            ->whereIn('item_id', $idsByType->flatten()->values()->all())
            ->get(['item_type', 'item_id'])
            ->map(fn ($row) => $row->item_type . ':' . $row->item_id)
            ->flip();

        $likeCounts = FeedReaction::query()
            ->where('reaction', 'like')
            ->whereIn('item_type', $idsByType->keys())
            ->whereIn('item_id', $idsByType->flatten()->values()->all())
            ->get(['item_type', 'item_id'])
            ->groupBy(fn ($row) => $row->item_type . ':' . $row->item_id)
            ->map(fn ($group) => $group->count());

        $commentCounts = FeedComment::query()
            ->whereIn('item_type', $idsByType->keys())
            ->whereIn('item_id', $idsByType->flatten()->values()->all())
            ->get(['item_type', 'item_id'])
            ->groupBy(fn ($row) => $row->item_type . ':' . $row->item_id)
            ->map(fn ($group) => $group->count());

        $feedComments = FeedComment::with('user:id,name')
            ->whereIn('item_type', $idsByType->keys())
            ->whereIn('item_id', $idsByType->flatten()->values()->all())
            ->latest()
            ->get([
                'id',
                'user_id',
                'parent_id',
                'item_type',
                'item_id',
                'body',
                'created_at',
            ]);

        $feedCommentIds = $feedComments->pluck('id')->values()->all();

        $feedCommentLikes = CommentLike::query()
            ->where('comment_type', 'feed_comment')
            ->whereIn('comment_id', $feedCommentIds)
            ->get(['comment_id', 'user_id']);

        $feedCommentLikeCounts = $feedCommentLikes
            ->groupBy('comment_id')
            ->map(fn ($group) => $group->count());

        $feedCommentLikedByUser = $feedCommentLikes
            ->where('user_id', $userId)
            ->pluck('comment_id')
            ->flip();

        $feedCommentsByItem = $feedComments
            ->map(function (FeedComment $comment) use ($feedCommentLikeCounts, $feedCommentLikedByUser) {
                return [
                    'id' => $comment->id,
                    'parent_id' => $comment->parent_id,
                    'body' => $comment->body,
                    'created_at' => $comment->created_at,
                    'user' => $comment->user ? [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                    ] : null,
                    'like_count' => (int) ($feedCommentLikeCounts[$comment->id] ?? 0),
                    'liked' => $feedCommentLikedByUser->has($comment->id),
                    'item_key' => $comment->item_type . ':' . $comment->item_id,
                ];
            })
            ->groupBy('item_key')
            ->map(function ($group) {
                $byId = $group->keyBy('id');
                $children = [];
                foreach ($group as $comment) {
                    if ($comment['parent_id']) {
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
                    ->take(3)
                    ->sortBy('created_at')
                    ->values();
            });

        $communityFeed = $communityFeed->map(function ($item) use ($acknowledged, $liked, $likeCounts, $commentCounts, $feedCommentsByItem) {
            $key = $item['type'] . ':' . $item['id'];
            $item['acknowledged'] = $acknowledged->has($key);
            $item['liked'] = $liked->has($key);
            $item['like_count'] = (int) ($likeCounts[$key] ?? 0);
            $item['comment_count'] = (int) ($commentCounts[$key] ?? 0);
            $item['comments'] = $feedCommentsByItem[$key] ?? [];
            return $item;
        });

        return Inertia::render('Dashboard', [
            'user' => $user->only(['id', 'name', 'email']),
            'favoriteLakes' => $favoriteLakes,
            'upcomingTrips' => $upcomingTrips,
            'recentReports' => $recentReports,
            'communityFeed' => $communityFeed,
        ]);
    }
}
