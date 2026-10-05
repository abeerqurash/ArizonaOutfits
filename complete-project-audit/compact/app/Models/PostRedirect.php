<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
class PostRedirect extends Model
{
    protected $fillable = [
        'post_id',
        'old_slug',
    ];
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}