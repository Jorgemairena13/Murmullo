<?php
// app/Http/Resources/PostResource.php
namespace App\Http\Resources;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\UserResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'texto' => $this->texto,
            // Covertir url
            'imagen_url' => $this->imagen ? Storage::url($this->imagen) : null,
            'created_at' => $this->created_at->diffForHumans(), // "hace 5 minutos"
            'user' => new UserResource($this->whenLoaded('user')),

            // Conteos de like y comentarios
            'likes_count' => $this->whenCounted('likes'),
            'comments_count' => $this->whenCounted('comments'),
        ];
    }
}
