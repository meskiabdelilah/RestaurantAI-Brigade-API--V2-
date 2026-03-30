<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plats()
    {
        return $this->hasMany(Plat::class);
    }
}
