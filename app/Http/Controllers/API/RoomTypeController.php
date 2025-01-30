<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\RoomTypeEditResource;
use App\Http\Resources\RoomTypeResource;
use App\Models\GuestHouseVariant;
use App\Models\PropertyGuestHouseType;
use App\Models\RoomTypeAmenity;
use App\Models\RoomType;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\RoomTypeImage;
use App\Models\RoomTypePlan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class RoomTypeController extends BaseController
{
    // List all rooms with RoomPlans
    public function listAllRoomTypes()
    {
        $rooms = RoomType::with(['type_name', 'images', 'amenities', 'plans'])->get();
        return $this->sendResponse(RoomTypeResource::collection($rooms), 'Rooms retrieved successfully.');
    }

    // Get rooms with search, pagination, and RoomPlans
    public function getAllRoomTypes(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $query = RoomType::query();

        $query->with(['property','amenities', 'images', 'plans']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('type_name', 'LIKE', "%{$search}%")
                    ->orWhere('room_size', 'LIKE', "%{$search}%")
                    ->orWhere('bed_type', 'LIKE', "%{$search}%")
                    ->orWhere('max_guests', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%")
                    ->orWhereHas('property', function ($propertyQuery) use ($search) {
                        $propertyQuery->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('amenities', function ($amenityQuery) use ($search) {
                        $amenityQuery->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        $rooms = $query->paginate($perPage);

        return $this->sendResponse([
            'roomtypes' => RoomTypeResource::collection($rooms),
            'pagination' => [
                'total' => $rooms->total(),
                'count' => $rooms->count(),
                'per_page' => $rooms->perPage(),
                'current_page' => $rooms->currentPage(),
                'total_pages' => $rooms->lastPage(),
            ],
        ], 'Rooms retrieved successfully.');
    }

    // Add a new room with RoomTypePlan
    public function addRoomType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255',
            'property_id' => 'required|exists:properties,id',
            'room_size' => 'required|numeric|min:0.1',
            'amenities' => 'required|array',
            'amenities.*.amenity_id' => 'distinct|exists:amenities,id',
            'images' => 'required|array',
            'images.*.image_url' => 'file|image|mimes:jpeg,png,jpg,gif|max:1000',
            'images.*.is_main' => 'nullable|in:true,false,1,0',

            'plans' => 'required_if:property_type,hotel|array',
            'plans.*.plan_type' => 'required|string|max:255',
            'plans.*.price' => 'required|numeric|min:1.0'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $validated = $validator->validated();

        $room = RoomType::create([
            'type_name'=> $request->type_name,
            'property_id'=> $request->property_id,
            'room_size'=> $request->room_size,
            'bed_type'=> $request->bed_type,
            'max_guests'=> $request->max_guests,
            'description'=> $request->description
        ]);

        // Attach amenities
        if (isset($validated['amenities'])) {

            foreach ($request->amenities as $amenityData) {
                RoomTypeAmenity::create([
                    'room_type_id' => $room->id,
                    'amenity_id' => $amenityData['amenity_id'],
                    'description' => $amenityData['description'] ?? null,
                ]);
            }
        }

        // Add plans (for hotel rooms only)
        if ($room->property->property_type === 'hotel' && isset($validated['plans'])) {
            foreach ($validated['plans'] as $planData) {
                $room->plans()->create($planData);
            }
        }

        // Save images
        if ($request->has('images')) {

            foreach ($request->images as $index => $imageData) {
                // Ensure that 'image_url' exists
                if (!isset($imageData['image_url'])) {
                    continue; // Skip invalid entries
                }

                // Set the first image as main and the rest as non-main
                $isMain = ($index == 0) ? true : false;

                $imageFile = $imageData['image_url'];

                // Generate a unique filename
                $imageName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Define the destination path
                $destinationPath = resource_path('uploads/room_images');

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the image to the destination path
                $imageFile->move($destinationPath, $imageName);

                // Create the image record
                $room->images()->create([
                    'image_url' => $imageName,
                    'is_main' => $isMain
                ]);

                // Mark that the main image has been set
                if ($index == 0) {
                    $mainImageSet = true;
                }
            }
        }

        return $this->sendResponse(new RoomTypeResource($room), 'Room added successfully.');
    }

    // Fetch a room by ID with RoomPlans
    public function getRoomTypeById($id)
    {
        $room = RoomType::with(['property', 'images', 'amenities', 'plans'])->find($id);

        if (is_null($room)) {
            return $this->sendError('Room not found.');
        }

        return $this->sendResponse(new RoomTypeEditResource($room), 'Room retrieved successfully.');
    }

    public function getRoomTypeDetails($id)
    {
        $room = RoomType::with(['property', 'images', 'amenities', 'plans'])->find($id);

        if (is_null($room)) {
            return $this->sendError('Room not found.');
        }

        return $this->sendResponse(new RoomTypeResource($room), 'Room retrieved successfully.');
    }

    // Update an existing room and its plans

    /**
     * @throws ValidationException
     */
    public function updateRoomType(Request $request, $id)
    {
        $room = RoomType::find($id);

        if (!$room) {
            return $this->sendError('Room not found.');
        }

        $validator = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255',
            'property_id' => 'required|exists:properties,id',
            'room_size' => 'required|numeric|min:0.1',
            'amenities' => 'required|array',
            'amenities.*.amenity_id' => 'distinct|exists:amenities,id',
            'images' => 'required|array',
            'images.*.image_url' => 'file|image|mimes:jpeg,png,jpg,gif|max:1000',
            'images.*.is_main' => 'nullable|in:true,false,1,0',

            'plans' => 'required_if:property_type,hotel|array',
            'plans.*.plan_type' => 'required|string|max:255',
            'plans.*.price' => 'required|numeric|min:1.0'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $validated = $validator->validated();

        $room->update([
            'type_name'=> $request->type_name ?? $room->type_name,
            'property_id'=> $request->property_id ?? $room->property_id,
            'room_size'=> $request->room_size ?? $room->room_size,
            'bed_type'=> $request->bed_type ?? $room->bed_type,
            'max_guests'=> $request->max_guests ?? $room->max_guests,
            'description'=> $request->description ?? $room->description
        ]);

        // Update amenities
        if (isset($validated['amenities'])) {

            $room->amenities()->detach();

            foreach ($request->amenities as $amenityData) {
                RoomTypeAmenity::create([
                    'room_type_id' => $room->id,
                    'amenity_id' => $amenityData['amenity_id'],
                    'description' => $amenityData['description'] ?? null,
                ]);
            }
        }

        // Update plans
        if ($room->property->property_type === 'hotel' && isset($validated['plans'])) {
            $room->plans()->delete();
            foreach ($validated['plans'] as $planData) {
                $room->plans()->create($planData);
            }
        }

        // Handle images (same as addRoom)
        if ($request->has('images')) {
            $room->images()->delete();

            foreach ($request->images as $index => $imageData) {

                // Set the first image as main and the rest as non-main
                $isMain = $index == 0;

                $imageFile = $imageData['image_url'];

                // Generate a unique filename
                $imageName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Define the destination path
                $destinationPath = resource_path('uploads/room_images');

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the image to the destination path
                $imageFile->move($destinationPath, $imageName);


                $room->images()->create([
                    'image_url' => $imageName,
                    'is_main' => $isMain,
                ]);
            }
        }

        return $this->sendResponse(new RoomTypeResource($room), 'Room updated successfully.');
    }
}

