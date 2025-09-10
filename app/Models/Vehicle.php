<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $table = 'vehicle';

     protected $fillable = [
        'brand',
        'model',
        'registration_number',
        'year', 
        'daily_price',
        'color',
        'mileage',
        'fuel_type',
        'transmission',
        'seats',
        'status'
    ];

    protected $casts = [
        'year' => 'integer',
        'daily_price' => 'integer',
        'mileage' => 'integer',
        'seats' => 'integer'
    ];

    #relacija korisnika sa vozilima preko rentiranja
    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isRented(): bool
    {
        return $this->status === 'rented';
    }

}
