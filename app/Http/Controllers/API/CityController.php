<?php

namespace App\Http\Controllers\API;

use App\Models\City;
use App\Http\Resources\CityResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CityController extends BaseController
{
    /**
     * List all cities without pagination.
     */
    public function listAllCities()
    {
        $cities = City::all();
        return $this->sendResponse(CityResource::collection($cities), 'Cities retrieved successfully.');
    }

    /**
     * List all cities with pagination and search functionality.
     */
    public function getAllCities(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default items per page
        $search = $request->query('search'); // Search query parameter

        $query = City::query();

        // Apply search filter if provided
        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        $cities = $query->paginate($perPage);

        return $this->sendResponse(
            [
                'cities' => CityResource::collection($cities),
                'pagination' => [
                    'total' => $cities->total(),
                    'count' => $cities->count(),
                    'per_page' => $cities->perPage(),
                    'current_page' => $cities->currentPage(),
                    'total_pages' => $cities->lastPage(),
                ],
            ],
            'Cities retrieved successfully with pagination and search.'
        );
    }

    /**
     * Add a new city.
     */
    public function addCity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:cities',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $city = City::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return $this->sendResponse(new CityResource($city), 'City created successfully.');
    }

    /**
     * Fetch a single city by ID.
     */
    public function getCityById($id)
    {
        $city = City::find($id);

        if (is_null($city)) {
            return $this->sendError('City not found.');
        }

        return $this->sendResponse(new CityResource($city), 'City retrieved successfully.');
    }

    /**
     * Update an existing city.
     */
    public function updateCity(Request $request, $id)
    {
        $city = City::find($id);

        if (is_null($city)) {
            return $this->sendError('City not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $city->name = $request->name;
        $city->description = $request->description;
        $city->save();

        return $this->sendResponse(new CityResource($city), 'City updated successfully.');
    }

    /**
     * Delete a city.
     */
    public function deleteCity($id)
    {
        $city = City::find($id);

        if (is_null($city)) {
            return $this->sendError('City not found.');
        }

        $city->delete();

        return $this->sendResponse([], 'City deleted successfully.');
    }
}
