<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\AmenityCategoryResource;
use App\Models\AmenityCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AmenityCategoryController extends BaseController
{
    // List all amenity categories
    public function listAllAmenityCategories()
    {
        $categories = AmenityCategory::all();
        return $this->sendResponse(AmenityCategoryResource::collection($categories), 'Amenity Categories retrieved successfully.');
    }

    // List amenity categories with pagination and search
    public function getAllAmenityCategories(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default 10 per page
        $search = $request->query('search');

        $query = AmenityCategory::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        $categories = $query->paginate($perPage);

        return $this->sendResponse(
            [
                'categories' => AmenityCategoryResource::collection($categories),
                'pagination' => [
                    'total' => $categories->total(),
                    'count' => $categories->count(),
                    'per_page' => $categories->perPage(),
                    'current_page' => $categories->currentPage(),
                    'total_pages' => $categories->lastPage(),
                ],
            ],
            'Amenity Categories retrieved successfully.'
        );
    }

    // Add a new amenity category
    public function addAmenityCategory(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:amenity_categories,name',  // Ensure the name is unique
            'type' => 'required|in:hotel,guesthouse,hotel and guesthouse,room,all',  // Ensure type is either 'hotel' or 'room'
        ]);

        // Return validation errors if they exist
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Create the new amenity category
        $category = AmenityCategory::create([
            'name' => $request->name,
            'type' => $request->type,  // Store the type value (hotel or room)
        ]);

        // Return the response with the newly created amenity category
        return $this->sendResponse(new AmenityCategoryResource($category), 'Amenity Category created successfully.');
    }


    // Fetch an amenity category by ID
    public function getAmenityCategoryById($id)
    {
        $category = AmenityCategory::find($id);

        if (is_null($category)) {
            return $this->sendError('Amenity Category not found.');
        }

        return $this->sendResponse(new AmenityCategoryResource($category), 'Amenity Category retrieved successfully.');
    }

    // Update an existing amenity category
    public function updateAmenityCategory(Request $request, $id)
    {
        // Find the amenity category by ID
        $category = AmenityCategory::find($id);

        // Return an error if the category is not found
        if (is_null($category)) {
            return $this->sendError('Amenity Category not found.');
        }

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:amenity_categories,name,' . $id, // Ensure the name is unique, excluding the current category
            'type' => 'required|in:hotel,guesthouse,hotel and guesthouse,room,all'  // Ensure type is either 'hotel' or 'room'
        ]);

        // Return validation errors if they exist
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        // Update the category with the new values
        $category->name = $request->name;
        $category->type = $request->type;  // Update the type
        $category->save();

        // Return the response with the updated amenity category
        return $this->sendResponse(new AmenityCategoryResource($category), 'Amenity Category updated successfully.');
    }


    // Delete an amenity category
    public function deleteAmenityCategory($id)
    {
        $category = AmenityCategory::find($id);

        if (is_null($category)) {
            return $this->sendError('Amenity Category not found.');
        }

        $category->delete();

        return $this->sendResponse([], 'Amenity Category deleted successfully.');
    }
}

