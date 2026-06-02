<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowRequest;
use App\Notifications\FollowRequestAccepted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowRequestController extends Controller
{
    public function index()
    {
        $requests = Auth::user()->followRequestsReceived()
            ->with('seguidor')
            ->latest()
            ->get();

        return response()->json([
            'data' => $requests->map(fn ($r) => [
                'id' => $r->id,
                'seguidor' => [
                    'id' => $r->seguidor->id,
                    'nombre' => $r->seguidor->nombre,
                    'username' => $r->seguidor->username,
                    'avatar_url' => $r->seguidor->avatar && str_starts_with($r->seguidor->avatar, 'http') ? $r->seguidor->avatar : null,
                ],
                'created_at' => $r->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function accept(FollowRequest $followRequest)
    {
        if ($followRequest->seguido_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        Auth::user()->followers()->syncWithoutDetaching([$followRequest->seguidor_id]);

        $seguidor = $followRequest->seguidor;

        $followRequest->delete();

        $seguidor->notify(new FollowRequestAccepted(Auth::user()));

        return response()->json(['message' => 'Solicitud aceptada'], 200);
    }

    public function reject(FollowRequest $followRequest)
    {
        if ($followRequest->seguido_id !== Auth::id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $followRequest->delete();

        return response()->json(['message' => 'Solicitud rechazada'], 200);
    }
}
