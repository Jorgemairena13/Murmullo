<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    /**
     *
     * @var array<int, string>
     */
    protected $table = 'posts';
    protected $fillable = [
        'texto',
        'imagen',
        'user_id',
    ];
    protected $appends = ['imagen_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function imagenUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::url($this->imagen),
        );
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    public function comentarios()
    {
        return $this->hasMany(Comment::class);
    }
}
