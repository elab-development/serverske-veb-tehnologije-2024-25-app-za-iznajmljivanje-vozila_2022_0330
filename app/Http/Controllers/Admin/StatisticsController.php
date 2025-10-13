<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    //statistika za vozila
    public function vehicleStatistics(Request $request)
    {
        $statistics = DB::table('rentals')
            ->join('vehicle', 'rentals.vehicle_id', '=', 'vehicle.id')
            ->join('users', 'rentals.user_id', '=', 'users.id')
            ->leftJoin('payments', 'rentals.id', '=', 'payments.rental_id')
            ->select(
                'vehicle.id as vehicle_id',
                'vehicle.brand',
                'vehicle.model',
                DB::raw('COUNT(rentals.id) as total_rentals'),
                DB::raw('COALESCE(SUM(payments.amount), 0) as total_revenue'),
                DB::raw('AVG(TIMESTAMPDIFF(DAY, rentals.start_date, rentals.end_date)) as avg_duration')
            )
            ->groupBy('vehicle.id', 'vehicle.brand', 'vehicle.model')
            ->orderByDesc('total_revenue')
            ->get();

        return response()->json($statistics);
    }

    //ugnježdena ruta - statistika za 1 vozilo
    public function vehicleRentalDetails($vehicleId)
    {
        $rentals = DB::table('rentals')
            ->join('users', 'rentals.user_id', '=', 'users.id')
            ->leftJoin('payments', 'rentals.id', '=', 'payments.rental_id')
            ->select(
                'rentals.id',
                'users.name as customer_name',
                'rentals.start_date',
                'rentals.end_date',
                'rentals.status',
                'payments.amount',
                'payments.payment_date'
            )
            ->where('rentals.vehicle_id', '=', $vehicleId)
            ->orderByDesc('rentals.start_date')
            ->get();

        return response()->json($rentals);
    }
}