<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Obtener mensajes de un departamento
     */
    public function index(Request $request)
    {
        $request->validate([
            'department_id' => 'required|integer|exists:departments,id'
        ]);

        $messages = Message::where('department_id', $request->department_id)
            ->orderBy('created_at', 'asc')
            ->limit(100)
            ->get();

        return response()->json($messages);
    }

    /**
     * Enviar un mensaje
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'department_id' => 'required|integer|exists:departments,id',
            'user_id' => 'required|integer|exists:users,id',
            'user_name' => 'required|string|max:100',
        ]);

        $message = Message::create($validated);

        // Broadcast a otros usuarios (no al que envió)
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => $message
        ], 201);
    }

    /**
     * Eliminar un mensaje
     */
    public function destroy(Message $message)
    {
        $message->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Mensaje eliminado'
        ]);
    }
}