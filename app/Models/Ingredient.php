<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'tags'];

    protected $casts = [
        'tags' => 'array'
    ];

    public function plates()
    {
        return $this->belongsToMany(Plat::class);
    }
}
