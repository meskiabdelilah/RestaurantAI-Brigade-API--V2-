<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    protected $fillable = ['name', 'tags'];

    protected $casts = [
        'tags' => 'array'
    ];

    public function plats()
    {
        return $this->belongsToMany(
            Plat::class,
            'plate_ingredient',
            'ingredient_id',
            'plate_id'
        );
    }
}
