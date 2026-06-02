<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FollowRequest;
use App\Models\User;
use App\Notifications\FollowRequestSent;
use App\Notifications\UserFollowed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{

    public function store(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo'], 403);
        }

        if ($user->is_private) {
            $existingRequest = FollowRequest::where('seguidor_id', auth()->id())
                ->where('seguido_id', $user->id)
                ->first();

            if ($existingRequest) {
                return response()->json([
                    'message' => 'Ya enviaste una solicitud a ' . $user->nombre,
                    'follow_request' => true,
                ], 200);
            }

            FollowRequest::create([
                'seguidor_id' => auth()->id(),
                'seguido_id' => $user->id,
            ]);

            $user->notify(new FollowRequestSent(Auth::user()));

            return response()->json([
                'message' => 'Solicitud enviada a ' . $user->nombre,
                'follow_request' => true,
            ], 201);
        }

        if (!auth()->user()->following()->whereKey($user->id)->exists()) {
            auth()->user()->following()->syncWithoutDetaching([$user->id]);
            $user->notify(new UserFollowed(Auth::user()));
        }

        return response()->json([
            'message' => 'Siguiendo a ' . $user->nombre,
            'follow_request' => false,
        ], 200);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes dejar seguirte a ti mismo'], 403);
        }

        FollowRequest::where('seguidor_id', auth()->id())
            ->where('seguido_id', $user->id)
            ->delete();

        auth()->user()->following()->detach($user->id);

        return response()->json([
            'message' => 'Dejaste de seguir a ' . $user->nombre,
        ], 200);
    }
}
