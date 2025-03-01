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
            // Return 10 popular cities when no query is provided
            $cities = City::limit(10)->withCount('hotels')->get();

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

        // Search for cities matching the query
        $cities = City::where('name', 'LIKE', "%{$query}%")
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

        // Search for properties matching the query
        $properties = Property::where('name', 'LIKE', "%{$query}%")
            ->orWhere('address', 'LIKE', "%{$query}%")
            ->orWhereHas('city', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
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
