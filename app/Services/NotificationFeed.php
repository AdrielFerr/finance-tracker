<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Collection;

class NotificationFeed
{
    private const RECENT_LIMIT = 10;

    public function push(User $user, string $title, string $body, ?string $url = null, string $level = 'info'): UserNotification
    {
        return UserNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'level' => $level,
        ]);
    }

    public function unreadCountFor(User $user): int
    {
        return UserNotification::forUser($user->id)->unread()->count();
    }

    /**
     * @return Collection<int, UserNotification>
     */
    public function recentFor(User $user): Collection
    {
        return UserNotification::forUser($user->id)
            ->latest()
            ->limit(self::RECENT_LIMIT)
            ->get();
    }

    public function markAsRead(User $user, int $notificationId): void
    {
        UserNotification::forUser($user->id)
            ->whereKey($notificationId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public function markAllAsRead(User $user): void
    {
        UserNotification::forUser($user->id)
            ->unread()
            ->update(['read_at' => now()]);
    }
}
