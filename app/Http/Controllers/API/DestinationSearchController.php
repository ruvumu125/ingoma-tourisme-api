<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Property;
use Illuminate\Http\Request;

class DestinationSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if (empty($query)) {
            // ✅ Fetch 10 cities that have at least 1 property
            $cities = City::whereHas('hotels') // Ensures the city has properties
            ->withCount('hotels')
                ->limit(10)
                ->get();

            foreach ($cities as $city) {
                $results[] = [
                    'type' => 'city',
                    'name' => $city->name,
                    'property_count' => $city->hotels_count,
                    'is_hotel' => false // ✅ Always false for cities
                ];
            }

            return response()->json($results);
        }

        // ✅ Fetch cities that match the query and have at least 1 property
        $cities = City::where('name', 'LIKE', "%{$query}%")
            ->whereHas('hotels') // Ensures the city has properties
            ->withCount('hotels')
            ->get();

        foreach ($cities as $city) {
            $results[] = [
                'type' => 'city',
                'name' => $city->name,
                'property_count' => $city->hotels_count,
                'is_hotel' => false // ✅ Always false for cities
            ];
        }

        // ✅ Fetch properties that match the query
        $properties = Property::where('name', 'LIKE', "%{$query}%")
            ->orWhere('address', 'LIKE', "%{$query}%")
            ->get();

        foreach ($properties as $property) {
            $results[] = [
                'type' => 'property',
                'name' => $property->name,
                'address' => $property->address,
                'is_hotel' => $property->hotelType()->exists() // ✅ Only for properties
            ];
        }

        return response()->json($results);
    }
}
