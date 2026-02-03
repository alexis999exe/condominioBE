<?php

namespace App\Http\Controllers\Api;

use App\Events\NotificationSent;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Listar notificaciones del usuario autenticado.
     * GET /api/notifications
     * Query params: ?read=0|1 (opcional, filtrar por estado leído)
     */
    public function index(Request $request)
    {
        $userId = $request->get('user_id'); // temporal hasta que haya auth real

        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Filtro opcional por estado leído
        if ($request->has('read')) {
            $query->where('read', (bool) $request->get('read'));
        }

        $notifications = $query->take(50)->get();

        $unreadCount = Notification::where('user_id', $userId)
            ->where('read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ]);
    }

    /**
     * Marcar una notificación como leída.
     * POST /api/notifications/{id}/read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $notification->markAsRead();

        return response()->json([
            'status' => 'success',
            'notification' => $notification->fresh(),
        ]);
    }

    /**
     * Marcar todas las notificaciones del usuario como leídas.
     * POST /api/notifications/read-all
     */
    public function markAllAsRead(Request $request)
    {
        $userId = $request->get('user_id');

        Notification::where('user_id', $userId)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Todas las notificaciones marcadas como leídas.',
        ]);
    }

    /**
     * Crear notificación (para testing / seed manual desde frontend).
     * POST /api/notifications
     * Body: { user_id, type, title, message, data (opcional) }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'type'    => 'required|in:mensaje,multa,asamblea,pago_atrasado',
            'title'   => 'required|string|max:200',
            'message' => 'required|string|max:1000',
            'data'    => 'nullable|array',
        ]);

        $notification = Notification::create($validated);

        // Broadcast por WebSocket al usuario
        broadcast(new NotificationSent($notification));

        return response()->json([
            'status'       => 'success',
            'notification' => $notification,
        ], 201);
    }
}
