<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Category;

class Post extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'expert',
        'feature_image',
        'template',
        'meta_title',
        'meta_description'
    ];

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'category_post',
            'post_id',
            'category_id'
        );
    }

    public function scopeLatestPosts($query, $limit = 3)
    {
        return $query->latest()->take($limit);
    }
}