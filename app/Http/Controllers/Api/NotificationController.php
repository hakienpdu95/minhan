<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use App\Shared\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->notifications()
            ->when(TenantContext::getOrganizationId(), fn ($q, $orgId) =>
                $q->where(fn ($sub) =>
                    $sub->where('organization_id', $orgId)->orWhereNull('organization_id')
                )
            )
            ->when($request->filter === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->when($request->type, fn ($q, $t) => $q->whereJsonContains('data->type', $t))
            ->latest()
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $query->getCollection()->map(fn ($n) => $this->format($n)),
            'meta' => [
                'total'        => $query->total(),
                'unread'       => $user->unreadNotifications()
                    ->when(TenantContext::getOrganizationId(), fn ($q, $orgId) =>
                        $q->where(fn ($sub) =>
                            $sub->where('organization_id', $orgId)->orWhereNull('organization_id')
                        )
                    )
                    ->count(),
                'current_page' => $query->currentPage(),
                'last_page'    => $query->lastPage(),
            ],
        ]);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()->unreadNotifications()
            ->when(TenantContext::getOrganizationId(), fn ($q, $orgId) =>
                $q->where(fn ($sub) =>
                    $sub->where('organization_id', $orgId)->orWhereNull('organization_id')
                )
            )
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markRead(Request $request, string $uuid): JsonResponse
    {
        $notification = $this->findForUser($request, $uuid);
        $notification->markAsRead();

        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()
            ->when(TenantContext::getOrganizationId(), fn ($q, $orgId) =>
                $q->where(fn ($sub) =>
                    $sub->where('organization_id', $orgId)->orWhereNull('organization_id')
                )
            )
            ->update(['read_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request, string $uuid): JsonResponse
    {
        $this->findForUser($request, $uuid)->delete();

        return response()->json(['ok' => true]);
    }

    public function pushSubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint'          => ['required', 'string', 'max:500'],
            'keys.p256dh'       => ['required', 'string'],
            'keys.auth'         => ['required', 'string'],
            'contentEncoding'   => ['nullable', 'string', 'max:20'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'user_id'          => $request->user()->id,
                'public_key'       => $request->input('keys.p256dh'),
                'auth_token'       => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding') ?: 'aesgcm',
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function pushUnsubscribe(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => ['required', 'string']]);

        PushSubscription::where('endpoint',  $request->input('endpoint'))
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['ok' => true]);
    }

    private function findForUser(Request $request, string $uuid): DatabaseNotification
    {
        return $request->user()
            ->notifications()
            ->where('uuid', $uuid)
            ->firstOrFail();
    }

    private function format(DatabaseNotification $n): array
    {
        $data = $n->data;

        return [
            'uuid'       => $n->uuid ?? $n->id,
            'type'       => $data['type']     ?? 'unknown',
            'title'      => $data['title']    ?? $data['message'] ?? '(Thông báo)',
            'body'       => $data['body']     ?? $data['message'] ?? '',
            'url'        => $data['url']      ?? '',
            'icon'       => $data['icon']     ?? 'bell',
            'severity'   => $data['severity'] ?? 'info',
            'read'       => $n->read_at !== null,
            'created_at' => $n->created_at->toISOString(),
            'time_ago'   => $n->created_at->diffForHumans(),
        ];
    }
}
