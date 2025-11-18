<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{

    public function store(User $user)
    {


        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes seguirte a ti mismo'], 403);
        }
        auth()->user()->following()->attach([$user->id]);
        // Seguri a usuario
        return response()->json([
            'message' => 'Siguiendo a ' . $user->nombre
        ], 200);
    }



    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'No puedes dejar seguirte a ti mismo'], 403);
        }
        auth()->user()->following()->detach($user->id);

        return response()->json([
            'message' => 'Dejaste de seguir a ' . $user->nombre,
        ], 200);
    }
}
