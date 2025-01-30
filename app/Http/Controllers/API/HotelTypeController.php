<?php

namespace App\Http\Controllers\API;

use App\Models\HotelType;
use App\Http\Resources\HotelTypeResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotelTypeController extends BaseController
{
    /**
     * List all hotel types without pagination.
     */
    public function listAllHotelTypes()
    {
        $hotelTypes = HotelType::all();
        return $this->sendResponse(HotelTypeResource::collection($hotelTypes), 'Hotel types retrieved successfully.');
    }

    /**
     * List all hotel types with pagination and search functionality.
     */
    public function getAllHotelTypes(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default items per page
        $search = $request->query('search'); // Search query parameter

        $query = HotelType::query();

        // Apply search filter if provided
        if ($search) {
            $query->where('type_name', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        $hotelTypes = $query->paginate($perPage);

        return $this->sendResponse(
            [
                'hotel_types' => HotelTypeResource::collection($hotelTypes),
                'pagination' => [
                    'total' => $hotelTypes->total(),
                    'count' => $hotelTypes->count(),
                    'per_page' => $hotelTypes->perPage(),
                    'current_page' => $hotelTypes->currentPage(),
                    'total_pages' => $hotelTypes->lastPage(),
                ],
            ],
            'Hotel types retrieved successfully with pagination and search.'
        );
    }

    /**
     * Add a new hotel type.
     */
    public function addHotelType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255|unique:hotel_types',
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $hotelType = HotelType::create([
            'type_name' => $request->type_name,
            'description' => $request->description,
        ]);

        return $this->sendResponse(new HotelTypeResource($hotelType), 'Hotel type created successfully.');
    }

    /**
     * Fetch a single hotel type by ID.
     */
    public function getHotelTypeById($id)
    {
        $hotelType = HotelType::find($id);

        if (is_null($hotelType)) {
            return $this->sendError('Hotel type not found.');
        }

        return $this->sendResponse(new HotelTypeResource($hotelType), 'Hotel type retrieved successfully.');
    }

    /**
     * Update an existing hotel type.
     */
    public function updateHotelType(Request $request, $id)
    {
        $hotelType = HotelType::find($id);

        if (is_null($hotelType)) {
            return $this->sendError('Hotel type not found.');
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255|unique:hotel_types,type_name,' . $hotelType->id,
            'description' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $hotelType->type_name = $request->type_name;
        $hotelType->description = $request->description;
        $hotelType->save();

        return $this->sendResponse(new HotelTypeResource($hotelType), 'Hotel type updated successfully.');
    }

    /**
     * Delete a hotel type.
     */
    public function deleteHotelType($id)
    {
        $hotelType = HotelType::find($id);

        if (is_null($hotelType)) {
            return $this->sendError('Hotel type not found.');
        }

        $hotelType->delete();

        return $this->sendResponse([], 'Hotel type deleted successfully.');
    }
}

