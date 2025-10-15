<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function convert($amount, $from, $to)
    {
        $response = Http::get("https://api.frankfurter.app/latest", [
            'amount' => $amount,
            'from' => strtoupper($from),
            'to' => strtoupper($to)
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Greška pri pozivu API-ja'], 500);
        }

        $data = $response->json();

        if (!isset($data['rates'][$to])) {
            return response()->json(['message' => 'Nevažeća valuta ili nepoznat par konverzije'], 400);
        }

        return response()->json([
            'original_amount' => $amount,
            'from_currency' => strtoupper($from),
            'to_currency' => strtoupper($to),
            'converted_amount' => $data['rates'][$to],
            'date' => $data['date']
        ]);
    }
}