<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'amount',
        'payment_date',
        'method'
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date'
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}