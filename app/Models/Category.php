<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Post;

class Category extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'parent_id',
        'meta_title',
        'meta_description',
        'expert'
    ];

    public function posts()
{
    return $this->belongsToMany(
        Post::class,
        'category_post',
        'category_id',
        'post_id'
    );
}

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}