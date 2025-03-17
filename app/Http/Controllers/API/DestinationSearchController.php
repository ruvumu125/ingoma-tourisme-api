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
        $propertyType = $request->input('property_type');

        // ✅ Ensure property type is valid
        if (!in_array($propertyType, ['hotel', 'guesthouse'])) {
            return response()->json(['error' => 'Invalid property type'], 400);
        }

        $results = [];

        // ✅ If query is empty, return 10 cities with at least one property of the given type
        if (empty($query)) {
            $cities = City::whereHas('hotels', function ($q) use ($propertyType) {
                $q->where('property_type', $propertyType);
            })
                ->withCount(['hotels' => function ($q) use ($propertyType) {
                    $q->where('property_type', $propertyType);
                }])
                ->limit(10)
                ->get();

            foreach ($cities as $city) {
                $results[] = [
                    'type' => 'city',
                    'name' => $city->name,
                    'property_count' => $city->hotels_count,
                    'is_hotel' => ($propertyType === 'hotel')
                ];
            }

            return response()->json($results); // ✅ Return only cities
        }

        // ✅ Fetch cities that match the query and have properties of the given type
        $cities = City::where('name', 'LIKE', "%{$query}%")
            ->whereHas('hotels', function ($q) use ($propertyType) {
                $q->where('property_type', $propertyType);
            })
            ->withCount(['hotels' => function ($q) use ($propertyType) {
                $q->where('property_type', $propertyType);
            }])
            ->get();

        foreach ($cities as $city) {
            $results[] = [
                'type' => 'city',
                'name' => $city->name,
                'property_count' => $city->hotels_count,
                'is_hotel' => ($propertyType === 'hotel')
            ];
        }

        // ✅ Fetch properties that match the query and belong to the given property type
        $properties = Property::where('property_type', $propertyType)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('address', 'LIKE', "%{$query}%");
            })
            ->get();

        foreach ($properties as $property) {
            $results[] = [
                'type' => 'property',
                'name' => $property->name,
                'address' => $property->address,
                'is_hotel' => ($propertyType === 'hotel')
            ];
        }

        // ✅ If no results found, return 10 cities with at least one property of the given type
        if (empty($results)) {
            $fallbackCities = City::whereHas('hotels', function ($q) use ($propertyType) {
                $q->where('property_type', $propertyType);
            })
                ->withCount(['hotels' => function ($q) use ($propertyType) {
                    $q->where('property_type', $propertyType);
                }])
                ->limit(10)
                ->get();

            foreach ($fallbackCities as $city) {
                $results[] = [
                    'type' => 'city',
                    'name' => $city->name,
                    'property_count' => $city->hotels_count,
                    'is_hotel' => ($propertyType === 'hotel')
                ];
            }
        }

        return response()->json($results);
    }





}
