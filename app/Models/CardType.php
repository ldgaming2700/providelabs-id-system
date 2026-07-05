<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CardType extends Model
{
    protected $fillable = [
        'name', 'slug', 'front_title', 'back_title',
        'primary_color', 'secondary_color', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function cardholders(): HasMany
    {
        return $this->hasMany(Cardholder::class);
    }
}
