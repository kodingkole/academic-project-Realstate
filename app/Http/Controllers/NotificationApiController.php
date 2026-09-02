<?php

namespace App\Http\Controllers;

use App\Models\InvestorNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationApiController extends Controller
{
    /**
     * Get notifications list & unread count for current authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notifications = InvestorNotification::where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $unreadCount = InvestorNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'data' => $notifications->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'message' => $item->message,
                    'type' => $item->type,
                    'is_read' => (bool) $item->is_read,
                    'created_at' => $item->created_at->diffForHumans(),
                    'timestamp' => $item->created_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $notification = InvestorNotification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        InvestorNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Real-time stream endpoint (SSE / Event-Stream) for live notification delivery.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();

        return response()->stream(function () use ($user) {
            $lastCheck = now();
            echo "retry: 3000\n\n";

            if ($user) {
                $unread = InvestorNotification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->latest()
                    ->get();

                echo "data: " . json_encode([
                    'type' => 'init',
                    'unread_count' => $unread->count(),
                    'latest' => $unread->first(),
                ]) . "\n\n";
            } else {
                echo "data: " . json_encode(['type' => 'guest']) . "\n\n";
            }

            ob_flush();
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
