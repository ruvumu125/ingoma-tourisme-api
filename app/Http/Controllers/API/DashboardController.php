<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getCounts()
    {
        $hotelsCount = Property::where('property_type', 'hotel')->count();
        $guesthouseCount = Property::where('property_type', 'guesthouse')->count();
        $propertiesCount = Property::count();
        $customersCount = User::where('role', 'customer')->count();

        return response()->json([
            'hotels' => $hotelsCount,
            'guesthouses' => $guesthouseCount,
            'properties' => $propertiesCount,
            'customers' => $customersCount,
        ]);
    }
}
