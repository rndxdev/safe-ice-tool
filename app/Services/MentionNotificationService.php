<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class MentionNotificationService
{
    /**
     * Parse text for @mentions and create notifications.
     *
     * @param string $text The comment/message text
     * @param User $author The user who wrote the text
     * @param string $contextTitle Title for the notification
     * @param string|null $url URL to link to
     * @param int|null $commentId The comment ID for deep linking
     */
    public function processText(
        string $text,
        User $author,
        string $contextTitle,
        ?string $url = null,
        ?int $commentId = null
    ): array {
        $finalUrl = $this->appendCommentAnchor($url, $commentId);
        $notifiedUsers = [];

        foreach ($this->extractMentions($text) as $username) {
            $user = $this->resolveNotifiable($username, $author);
            if (!$user) {
                continue;
            }

            $this->createMentionNotification($user, $author, $contextTitle, $finalUrl, $commentId);
            $notifiedUsers[] = $user->id;
        }

        return $notifiedUsers;
    }

    private function appendCommentAnchor(?string $url, ?int $commentId): ?string
    {
        if (!$url || !$commentId) {
            return $url;
        }
        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . 'comment=' . $commentId;
    }

    private function resolveNotifiable(string $username, User $author): ?User
    {
        if (strtolower($username) === strtolower($author->username ?? '')) {
            return null;
        }

        $user = User::where('username', $username)->first();

        if (!$user || !$user->wantsMentionNotifications()) {
            return null;
        }

        return $user;
    }

    private function createMentionNotification(
        User $user,
        User $author,
        string $contextTitle,
        ?string $finalUrl,
        ?int $commentId
    ): void {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'mention',
            'title' => 'You were mentioned',
            'message' => sprintf('@%s mentioned you in %s', $author->username ?? $author->name, $contextTitle),
            'url' => $finalUrl ?? route('dashboard'),
            'data' => [
                'author_id' => $author->id,
                'author_username' => $author->username,
                'author_name' => $author->name,
                'comment_id' => $commentId,
            ],
        ]);
    }

    /**
     * Extract @mentions from text.
     *
     * @param string $text
     * @return array List of usernames (without @)
     */
    public function extractMentions(string $text): array
    {
        // Match @username (alphanumeric, dash, underscore)
        preg_match_all('/@([a-zA-Z0-9_-]+)/', $text, $matches);

        return array_unique($matches[1] ?? []);
    }
}
