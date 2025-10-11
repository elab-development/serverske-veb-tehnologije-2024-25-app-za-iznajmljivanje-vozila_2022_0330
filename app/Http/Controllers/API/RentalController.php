<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rental;
use Illuminate\Support\Facades\Validator;

class RentalController extends Controller
{
    // Prikaz svih rentiranja
    public function index()
    {
        $rentals = Rental::with(['user', 'vehicle'])->get();
        return response()->json($rentals, 200);
    }

    // Prikaz jednog rentiranja
    public function show($id)
    {
        $rental = Rental::with(['user', 'vehicle'])->find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rentiranje nije pronađeno'], 404);
        }

        $priceInEUR = $rental->getTotalPriceInEUR();


        return response()->json([
        'renta' => $rental,
        'cena_u_evrima' => $priceInEUR
        ], 200);
    }

    // Kreiranje rentiranja
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'    => 'required|exists:users,id',
            'vehicle_id' => 'required|exists:vehicle,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'total_price'=> 'required|integer|min:0',
            'status'     => 'in:na_cekanju,placena,otkazana'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Greška pri validaciji',$validator->errors()], 422);
        }

        $rental = Rental::create($validator->validated());

        return response()->json($rental, 201);
    }

    // Ažuriranje
    public function update(Request $request, $id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rentiranje nije pronađeno'], 404);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'date|after_or_equal:today',
            'end_date'   => 'date|after_or_equal:start_date',
            'total_price'=> 'integer|min:0',
            'status'     => 'in:na_cekanju,placena,otkazana'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $rental->update($validator->validated());

        return response()->json($rental, 200);
    }

    //Brisanje rentiranja
    public function destroy($id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rentiranje nije pronađeno'], 404);
        }

        $rental->delete();

        return response()->json(['message' => 'Rentiranje obrisano'], 200);
    }

}
