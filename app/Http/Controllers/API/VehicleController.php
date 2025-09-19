<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{

     #Prikaz liste svih vozila

    public function index()
    {
        $vehicles = Vehicle::all();

        return response()->json([
            'message' => 'Lista vozila',
            'data' => $vehicles
        ], 200);
    }

   
    #Prikaz detalja jednog vozila
    
    public function show($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response()->json([
                'message' => 'Vozilo nije pronađeno'
            ], 404);
        }

        return response()->json([
            'message' => 'Detalji o vozilu',
            'data' => $vehicle
        ], 200);
    }
}
