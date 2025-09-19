<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'vehicle_id', 
        'start_date',
        'end_date',
        'total_price',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_price' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(){
        return $this->belongsTo(Vehicle::class);
    }

    public function isNaCekanju(): bool
    {
        return $this->status === 'na_cekanju';
    }

    public function isPlacena(): bool
    {
        return $this->status === 'placena';
    }

    public function isOtkazana(): bool
    {
        return $this->status === 'otkazana';
    }

    public function getDaysAttribute(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }

    public function isActive(): bool
    {
        return $this->isPlacena() && 
               now()->between($this->start_date, $this->end_date);
    }

}
