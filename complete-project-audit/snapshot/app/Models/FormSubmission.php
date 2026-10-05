<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmission extends Model
{
    protected $fillable = [
        'form_source',
        'form_type',
        'full_name',
        'first_name',
        'last_name',
        'subject',
        'email',
        'phone',
        'message',
        'blog_title',
        'blog_slug',
    ];
}