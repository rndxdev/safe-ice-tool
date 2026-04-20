<?php

namespace App\Http\Controllers;

use App\Models\FeedAcknowledgement;
use App\Models\FeedComment;
use App\Models\FeedReaction;
use App\Services\MentionNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedInteractionController extends Controller
{
    private const ALLOWED_TYPES = ['report', 'post', 'comment', 'trip_share', 'lake'];

    public function acknowledge(Request $request)
    {
        $data = $request->validate([
            'item_type' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_TYPES)],
            'item_id' => ['required', 'integer', 'min:1'],
        ]);

        FeedAcknowledgement::updateOrCreate([
            'user_id' => Auth::id(),
            'item_type' => $data['item_type'],
            'item_id' => $data['item_id'],
        ], [
            'acknowledged_at' => now(),
        ]);

        return back();
    }

    public function toggleLike(Request $request)
    {
        $data = $request->validate([
            'item_type' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_TYPES)],
            'item_id' => ['required', 'integer', 'min:1'],
        ]);

        $existing = FeedReaction::query()
            ->where('user_id', Auth::id())
            ->where('item_type', $data['item_type'])
            ->where('item_id', $data['item_id'])
            ->where('reaction', 'like')
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            FeedReaction::create([
                'user_id' => Auth::id(),
                'item_type' => $data['item_type'],
                'item_id' => $data['item_id'],
                'reaction' => 'like',
            ]);
        }

        return back();
    }

    public function comment(Request $request)
    {
        $data = $request->validate([
            'item_type' => ['required', 'string', 'in:' . implode(',', self::ALLOWED_TYPES)],
            'item_id' => ['required', 'integer', 'min:1'],
            'body' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $this->assertParentBelongsToItem($data);

        $comment = FeedComment::create([
            'user_id' => Auth::id(),
            'item_type' => $data['item_type'],
            'item_id' => $data['item_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        app(MentionNotificationService::class)->processText(
            $data['body'],
            Auth::user(),
            'a comment',
            $this->getUrlForItem($data['item_type'], $data['item_id']),
            $comment->id
        );

        return back();
    }

    private function assertParentBelongsToItem(array $data): void
    {
        if (empty($data['parent_id'])) {
            return;
        }

        FeedComment::query()
            ->where('id', $data['parent_id'])
            ->where('item_type', $data['item_type'])
            ->where('item_id', $data['item_id'])
            ->firstOrFail();
    }

    private function getUrlForItem(string $type, int $id): ?string
    {
        // Posts have dedicated pages, everything else appears on dashboard feed
        return match ($type) {
            'post' => route('posts.show', $id),
            default => route('dashboard'),
        };
    }
}
