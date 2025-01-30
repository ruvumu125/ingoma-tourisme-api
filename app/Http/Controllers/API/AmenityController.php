<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\AmenityResource;
use App\Models\Amenity;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmenityController extends BaseController
{
    // List all amenities
    public function listAllAmenities()
    {
        $amenities = Amenity::with('category')->get();
        return $this->sendResponse(AmenityResource::collection($amenities), 'Amenities retrieved successfully.');
    }

    public function propertyAmenitiesList()
    {
        // Fetch amenities based on the related category's type
        $amenities = Amenity::whereHas('category', function ($query) {
            $query->whereIn('type', ['hotel', 'guesthouse', 'hotel and guesthouse', 'all']);
        })->get();

        // Return a response with the AmenityResource collection
        return $this->sendResponse(AmenityResource::collection($amenities), 'Amenities retrieved successfully.');
    }

    public function roomAmenitiesList()
    {
        // Fetch amenities based on the related category's type
        $amenities = Amenity::whereHas('category', function ($query) {
            $query->whereIn('type', ['room', 'all']);
        })->get();

        // Return a response with the AmenityResource collection
        return $this->sendResponse(AmenityResource::collection($amenities), 'Amenities retrieved successfully.');
    }

    // List amenities with pagination and search
    public function getAllAmenities(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default 10 per page
        $search = $request->query('search');

        $query = Amenity::with('category');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                ->orWhereHas('category', function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%{$search}%");
                });
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        $amenities = $query->paginate($perPage);

        return $this->sendResponse(
            [
                'amenities' => AmenityResource::collection($amenities),
                'pagination' => [
                    'total' => $amenities->total(),
                    'count' => $amenities->count(),
                    'per_page' => $amenities->perPage(),
                    'current_page' => $amenities->currentPage(),
                    'total_pages' => $amenities->lastPage(),
                ],
            ],
            'Amenities retrieved successfully.'
        );
    }

    // Add a new amenity
    public function addAmenity(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:amenities,name',
            'amenity_category_id' => 'required|exists:amenity_categories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $amenity = Amenity::create([
            'name' => $request->name,
            'amenity_category_id' => $request->amenity_category_id,
        ]);

        return $this->sendResponse(new AmenityResource($amenity), 'Amenity created successfully.');
    }

    // Fetch an amenity by ID
    public function getAmenityById($id)
    {
        $amenity = Amenity::with('category')->find($id);

        if (is_null($amenity)) {
            return $this->sendError('Amenity not found.');
        }

        return $this->sendResponse(new AmenityResource($amenity), 'Amenity retrieved successfully.');
    }

    // Update an existing amenity
    public function updateAmenity(Request $request, $id)
    {
        $amenity = Amenity::find($id);

        if (is_null($amenity)) {
            return $this->sendError('Amenity not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:amenities,name,' . $id,
            'amenity_category_id' => 'required|exists:amenity_categories,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $amenity->name = $request->name;
        $amenity->amenity_category_id = $request->amenity_category_id;
        $amenity->save();

        return $this->sendResponse(new AmenityResource($amenity), 'Amenity updated successfully.');
    }

    // Delete an amenity
    public function deleteAmenity($id)
    {
        $amenity = Amenity::find($id);

        if (is_null($amenity)) {
            return $this->sendError('Amenity not found.');
        }

        $amenity->delete();

        return $this->sendResponse([], 'Amenity deleted successfully.');
    }
}

