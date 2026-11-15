<?php

namespace App\Http\Controllers;

use App\Services\NotificationFeed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private NotificationFeed $feed) {}

    public function open(int $notification): RedirectResponse
    {
        $user = Auth::user();

        $this->feed->markAsRead($user, $notification);

        $target = $this->feed->recentFor($user)->firstWhere('id', $notification)?->url;

        return redirect($target ?: route('expenses.index'));
    }

    public function markAllAsRead(): RedirectResponse
    {
        $this->feed->markAllAsRead(Auth::user());

        return back();
    }
}
