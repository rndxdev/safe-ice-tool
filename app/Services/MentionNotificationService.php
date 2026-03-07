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
        $mentionedUsernames = $this->extractMentions($text);
        $notifiedUsers = [];

        // Append comment anchor to URL if we have a comment ID
        $finalUrl = $url;
        if ($finalUrl && $commentId) {
            $separator = str_contains($finalUrl, '?') ? '&' : '?';
            $finalUrl .= $separator . 'comment=' . $commentId;
        }

        foreach ($mentionedUsernames as $username) {
            // Don't notify yourself
            if (strtolower($username) === strtolower($author->username ?? '')) {
                continue;
            }

            $user = User::where('username', $username)->first();

            if (!$user) {
                continue;
            }

            // Check if user wants mention notifications
            if (!$user->wantsMentionNotifications()) {
                continue;
            }

            // Create notification
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

            $notifiedUsers[] = $user->id;
        }

        return $notifiedUsers;
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
