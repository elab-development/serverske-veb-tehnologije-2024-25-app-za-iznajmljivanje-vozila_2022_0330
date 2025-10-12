<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\Payment;
use App\Models\Rental;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(Request $request, $rentalId)
    {
        DB::beginTransaction();

        try {
            $rental = Rental::findOrFail($rentalId);

            //racunanje iznosa rente
            $days = $rental->start_date->diffInDays($rental->end_date) + 1;
            $amount = $days * $rental->vehicle->daily_price;

            $payment = Payment::create([
                'rental_id' => $rental->id,
                'amount' => $amount,
                'payment_date' => now(),
                'method' => $request->method ?? 'kartica'
            ]);

            //ažuriranje statusa
            $rental->update(['status' => 'placena']);
            $rental->vehicle->update(['status' => 'rented']);

            DB::commit();

            return response()->json([
                'message' => 'Plaćanje uspešno evidentirano.',
                'payment' => $payment
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        $payments = Payment::with(['rental.vehicle', 'rental.user'])->get();
        return response()->json($payments);
    }
}