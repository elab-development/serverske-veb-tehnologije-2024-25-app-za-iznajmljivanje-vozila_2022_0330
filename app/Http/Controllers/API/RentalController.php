<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rental;
use Illuminate\Support\Facades\Validator;
use App\Models\Vehicle;

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
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Greška pri validaciji',$validator->errors()], 422);
        }

        $data = $validator->validated();
        $vehicle = Vehicle::findOrFail($data['vehicle_id']);

        //racunanje cene rente
        $days = (new \Carbon\Carbon($data['start_date']))->diffInDays(new \Carbon\Carbon($data['end_date'])) + 1;
        $totalPrice = $days * $vehicle->daily_price;

        $rental = Rental::create([
            'user_id' => $data['user_id'],
            'vehicle_id' => $data['vehicle_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_price' => $totalPrice,
            'status' => 'na_cekanju'
        ]);

        return response()->json([
            'message' => 'Rezervacija uspešno kreirana',
            'rental'=>$rental], 201);
    }

    // Ažuriranje
    public function update(Request $request, $id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rentiranje nije pronađeno'], 404);
        }

        if ($rental->isPlacena()) {
            return response()->json(['message' => 'Plaćena rezervacija se ne može menjati'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'date|after_or_equal:today',
            'end_date'   => 'date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $validator->validated();

        // Ako su datumi promenjeni, izračunaj novu cenu
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $days = (new \Carbon\Carbon($data['start_date']))->diffInDays(new \Carbon\Carbon($data['end_date'])) + 1;
            $data['total_price'] = $days * $rental->vehicle->daily_price;
        }

        $rental->update($data);

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

    //otkazivanje rentiranja
    public function cancel($id)
    {
        $rental = Rental::find($id);

        if (!$rental) {
            return response()->json(['message' => 'Rezervacija nije pronađena'], 404);
        }

        $rental->update(['status' => 'otkazana']);

        // oslobađanje vozila
        $rental->vehicle->update(['status' => 'available']);

        return response()->json(['message' => 'Rezervacija je otkazana i vozilo je slobodno.'], 200);
    }

    //pregled prethodnih rezervacija
    public function myRentals(Request $request)
    {
        $user = $request->user();

        $rentals = Rental::with(['vehicle'])
            ->where('user_id', $user->id)
            ->orderBy('start_date', 'desc')
            ->get();

        if ($rentals->isEmpty()) {
            return response()->json(['message' => 'Nemate prethodnih rezervacija.'], 200);
        }

        return response()->json([
            'message' => 'Lista vaših prethodnih rezervacija',
            'data' => $rentals
        ], 200);
    }

    //izmena rezervacije
    public function updateMyRental(Request $request, $id)
    {
        $user = $request->user();
        $rental = Rental::where('id', $id)->where('user_id', $user->id)->first();

        if (!$rental) {
            return response()->json(['message' => 'Rezervacija nije pronađena ili ne pripada vama.'], 404);
        }

        // Dozvoljena izmena samo ako je status "na_cekanju"
        if ($rental->status !== 'na_cekanju') {
            return response()->json(['message' => 'Rezervaciju nije moguće izmeniti jer je već plaćena ili otkazana.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'date|after_or_equal:today',
            'end_date'   => 'date|after_or_equal:start_date',
            'total_price'=> 'integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Greška pri validaciji',
                'errors' => $validator->errors()
            ], 422);
        }

        $rental->update($validator->validated());

        return response()->json([
            'message' => 'Rezervacija uspešno ažurirana',
            'data' => $rental
        ], 200);
    }

}
