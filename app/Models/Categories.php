<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'url', 'title', 'meta_description', 'parent_id', 'level',
    ];
    public function app()
    {
        return $this->belongsToMany('App\Models\App');
    }
}
