<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = ['name', 'location', 'cuisine'];

    public function orders(): HasMany { return $this->hasMany(Order::class); }

}
