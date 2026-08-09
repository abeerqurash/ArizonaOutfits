<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Notifications\AdminSystemNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class AdminNotificationController extends AdminController
{
    public function index(Request $request): View
    {
        $filter = $request->string('status')->value();
        $query = $request->user()->notifications();

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'all' => $request->user()->notifications()->count(),
            'unread' => $request->user()->unreadNotifications()->count(),
            'read' => $request->user()->readNotifications()->count(),
        ];

        $admins = User::query()
            ->where('is_admin', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.notifications.index', compact(
            'notifications',
            'stats',
            'admins',
            'filter'
        ));
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        $item = $this->ownedNotification($request, $notification);
        $item->markAsRead();

        $url = data_get($item->data, 'action_url');
        if (is_string($url) && $this->safeInternalUrl($url)) {
            return redirect()->to($url);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function destroy(Request $request, string $notification): RedirectResponse
    {
        $this->ownedNotification($request, $notification)->delete();

        return back()->with('success', 'Notification deleted.');
    }

    public function clearRead(Request $request): RedirectResponse
    {
        $request->user()->readNotifications()->delete();

        return back()->with('success', 'Read notifications cleared.');
    }

    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'recipient' => ['required', 'string', 'in:all,specific'],
            'user_ids' => ['nullable', 'array', 'required_if:recipient,specific'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'level' => ['required', 'in:info,success,warning,danger'],
            'action_url' => ['nullable', 'string', 'max:1000'],
            'action_label' => ['nullable', 'string', 'max:60'],
        ]);

        if (!empty($validated['action_url']) && !$this->safeInternalUrl($validated['action_url'])) {
            return back()
                ->withErrors(['action_url' => 'Use an internal URL beginning with / or the store URL.'])
                ->withInput();
        }

        $admins = User::query()
            ->where('is_admin', true)
            ->where('status', 'active')
            ->when(
                $validated['recipient'] === 'specific',
                fn ($query) => $query->whereIn('id', $validated['user_ids'] ?? [])
            )
            ->get();

        if ($admins->isEmpty()) {
            return back()->withErrors(['user_ids' => 'No active administrators were selected.'])->withInput();
        }

        Notification::send($admins, new AdminSystemNotification(
            $validated['title'],
            $validated['message'],
            $validated['level'],
            $validated['action_url'] ?? null,
            $validated['action_label'] ?? null,
        ));

        return back()->with('success', 'Notification sent to ' . $admins->count() . ' administrator(s).');
    }

    private function ownedNotification(Request $request, string $id): DatabaseNotification
    {
        return $request->user()->notifications()->whereKey($id)->firstOrFail();
    }

    private function safeInternalUrl(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && strcasecmp($host, request()->getHost()) === 0;
    }
}
