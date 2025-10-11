<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;

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

    public function getTotalPriceInEUR() {
     try {
        $response = Http::get('https://api.exchangerate.host/convert', [
            'from' => 'RSD',
            'to' => 'EUR',
            'amount' => $this->total_price,
            'access_key' => env('EXCHANGE_API_KEY')
        ]);

        if ($response->failed()) {
            return ['error' => 'Greška prilikom poziva API-ja.'];
        }

        $data = $response->json();

        if (!isset($data['result'])) {
            return ['error' => 'Nevažeći odgovor API-ja.', 'details' => $data];
        }

        $rate = $data['info']['rate'] ?? null;

        return [
            'original' => $this->total_price . ' RSD',
            'converted' => round($data['result'], 2) . ' EUR',
            'rate' => $rate ? round($rate, 4) : 'podatak nije dostupan'
        ];
    } catch (\Exception $e) {
        return ['error' => 'Greška: ' . $e->getMessage()];
    }
    }
}
