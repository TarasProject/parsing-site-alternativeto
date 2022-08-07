<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alternativeto extends Model
{
    protected $table = 'alternativeto';
    protected $fillable = [
        'os', 'url', 'url_hash', 'title', 'like', 'icon', 'anonce', 'company_website', 'description',
    ];
}
