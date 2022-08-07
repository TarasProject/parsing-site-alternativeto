<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    protected $table = 'app';
    protected $fillable = [
        'url', 'url_hash', 'title', 'page', 'like', 'icon', 'anonce', 'company_website', 'description', 'tags','platforms','company','website','count_alternatives',
        'categories', 'sceenshots_urls', 'alternatives', 'license','app_stores', 'meta_description', 'ratings'
    ];
    public function categories()
    {
        return $this->belongsToMany('App\Models\Categories');
    }
}
