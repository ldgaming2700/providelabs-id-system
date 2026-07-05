<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Cardholder extends Model
{
    protected $fillable = [
        'card_type_id', 'registered_by', 'id_no', 'name', 'sc_id',
        'philhealth', 'cellphone_no', 'address', 'position', 'birthday',
        'contact_name', 'emergency_contact_number', 'relationship',
        'photo_path', 'photo_status', 'status', 'generated_at',
        'printed_at', 'released_at',
    ];

    protected $casts = [
        'birthday' => 'date',
        'generated_at' => 'datetime',
        'printed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    protected $appends = ['age', 'photo_url'];

    public function cardType(): BelongsTo
    {
        return $this->belongsTo(CardType::class);
    }

    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birthday ? Carbon::parse($this->birthday)->age : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
    if (! $this->photo_path) {
        return null;
        }
    
    return route('cardholders.photo', $this);
    }

    public function hasUploadedPhoto(): bool
    {
        return $this->photo_status === 'uploaded' && filled($this->photo_path);
    }
}
