<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\PropertyDetailsResource;
use App\Http\Resources\PropertyEditResource;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\RoomTypeResource;
use App\Models\GuestHouseVariant;
use App\Models\PropertyAmenity;
use App\Models\PropertyGuestHouseType;
use App\Models\PropertyHotelType;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyRule;
use App\Models\HotelType;
use App\Models\City;
use App\Models\Landmark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PropertyController extends BaseController
{
    // List all hotels
    public function listAllProperties()
    {
        $hotels = Property::with(['city', 'amenities', 'images', 'landmarks', 'rules'])->get();
        return $this->sendResponse(PropertyResource::collection($hotels), 'Hotels retrieved successfully.');
    }


    // Get all hotels with search by name, city name, and hotel type name
    public function getAllProperties(Request $request)
    {
        $perPage = $request->query('per_page', 10); // Default to 10 items per page
        $search = $request->query('search'); // Search query parameter

        $query = Property::query();

        // Eager load relationships for city, hotelType, and amenities
        $query->with(['city', 'amenities', 'images', 'landmarks', 'rules']);

        // Apply search filter if the search parameter is provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%") // Search by hotel name
                ->orWhere('property_type', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%") // Search by description
                ->orWhere('address', 'LIKE', "%{$search}%") // Search by address
                ->orWhereHas('city', function ($cityQuery) use ($search) { // Search by city name
                    $cityQuery->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('amenities', function ($amenityQuery) use ($search) { // Search by amenity name
                    $amenityQuery->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Order by created_at in descending order
        $query->orderBy('created_at', 'desc');

        $hotels = $query->paginate($perPage);

        return $this->sendResponse(
            [
                'properties' => PropertyResource::collection($hotels),
                'pagination' => [
                    'total' => $hotels->total(),
                    'count' => $hotels->count(),
                    'per_page' => $hotels->perPage(),
                    'current_page' => $hotels->currentPage(),
                    'total_pages' => $hotels->lastPage(),
                ],
            ],
            'Properties retrieved successfully.'
        );
    }


    // Add a new hotel
    public function addProperty(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'property_type' => 'required|in:hotel,guesthouse',
            'description' => 'required|string',
            'address' => 'required|string',
            'city_id' => 'required|exists:cities,id',
            'whatsapp_number1' => 'required|string|max:20',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rules' => 'required|array',
            'rules.*.rule_description' => 'required|string',
            'landmarks' => 'required|array',
            'landmarks.*.name' => 'required|string|max:255',
            'landmarks.*.distance' => 'required|string',
            'amenities' => 'required|array',
            'amenities.*.amenity_id' => 'distinct|exists:amenities,id',
            'images' => 'required|array',
            'images.*.image_url' => 'file|image|mimes:jpeg,png,jpg,gif|max:1000',
            'images.*.is_main' => 'nullable|in:true,false,1,0',

            'hotel_type' => 'required_if:property_type,hotel|exists:hotel_types,id',
            'guest_house_variants' => 'required_if:property_type,guesthouse|array',
            'guest_house_variants.*.variant' => 'required|string',
            'guest_house_variants.*.price' => 'required|numeric|min:1',
            'guest_house_variants.*.currency' => 'required|string|in:bif,dollar',
            'guest_house_variants.*.tarification_type' => 'required|string|in:monthly,daily',


        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $validated = $validator->validated();

        // Create the hotel
        $property = Property::create([
            'name' => $request->name,
            'description' => $request->description,
            'address' => $request->address,
            'city_id' => $request->city_id,
            'property_type' => $request->property_type,
            'whatsapp_number1' => $request->whatsapp_number1,
            'whatsapp_number2' => $request->whatsapp_number2,
            'latitude'=> $request->latitude,
            'longitude'=> $request->longitude,
            'rating' => $request->rating,
            'is_active' => true,
        ]);


        // Attach amenities
        if (isset($validated['amenities'])) {

            foreach ($request->amenities as $amenityData) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity_id' => $amenityData['amenity_id'],
                    'description' => $amenityData['description'] ?? null,
                ]);
            }
        }

        // Add rules
        if (isset($validated['rules'])) {
            foreach ($validated['rules'] as $rule) {
                $property->rules()->create(['rule_description' => $rule['rule_description']]);
            }
        }

        // Add landmarks
        if (isset($validated['landmarks'])) {
            foreach ($validated['landmarks'] as $landmarkData) {
                $property->landmarks()->create($landmarkData);
            }
        }

        // Handle hotels
        if ($validated['property_type'] === 'hotel') {

            PropertyHotelType::create([
                'property_id' => $property->id,
                'hotel_type_id' => $validated['hotel_type'],
            ]);
        }

        // Handle guest houses
        if ($validated['property_type'] === 'guesthouse') {
            PropertyGuestHouseType::create([
                'property_id' => $property->id
            ]);
        }

        // Add guest house variants (for guesthouse rooms only)
        if ($validated['property_type'] === 'guesthouse' && isset($validated['guest_house_variants'])) {
            //$property = Property::find($property->id);
            $guestHouseType = $property->guestHouseType; // Get the guest house type for the property

            if ($guestHouseType) {
                foreach ($validated['guest_house_variants'] as $variant) {
                    GuestHouseVariant::create([
                        'property_guest_house_id' => $guestHouseType->id,
                        'variant' => $variant['variant'],
                        'price' => $variant['price'],
                        'currency' => $variant['currency'],
                        'tarification_type' => $variant['tarification_type']
                    ]);
                }
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
                $destinationPath = resource_path('uploads/property_images');

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the image to the destination path
                $imageFile->move($destinationPath, $imageName);

                // Create the image record
                $property->images()->create([
                    'image_url' => $imageName,
                    'is_main' => $isMain
                ]);

                // Mark that the main image has been set
                if ($index == 0) {
                    $mainImageSet = true;
                }
            }
        }



        return $this->sendResponse(new PropertyResource($property), 'Property added successfully.');
    }

    // Fetch a hotel by ID
    public function getPropertyById($id)
    {
        $hotel = Property::with(['hotelType','city','rules','landmarks','amenities','guestHouseVariants', 'images'])->find($id);

        if (is_null($hotel)) {
            return $this->sendError('Property not found.');
        }

        return $this->sendResponse(new PropertyEditResource($hotel), 'Property retrieved successfully.');
    }

    public function getPropertyDetails($id)
    {
        $hotel = Property::with(['hotelType','city','rules','landmarks','amenities','guestHouseVariants', 'images'])->find($id);

        if (is_null($hotel)) {
            return $this->sendError('Property not found.');
        }

        return $this->sendResponse(new PropertyResource($hotel), 'Property retrieved successfully.');
    }

    // Update an existing hotel
    public function updateProperty(Request $request, $id)
    {
        $property = Property::find($id);

        if (!$property) {
            return $this->sendError('Property not found.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'property_type' => 'sometimes|required|in:hotel,guesthouse',
            'description' => 'sometimes|required|string',
            'address' => 'sometimes|required|string',
            'city_id' => 'sometimes|required|exists:cities,id',
            'whatsapp_number1' => 'sometimes|required|string|max:20',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rules' => 'sometimes|array',
            'rules.*.rule_description' => 'required|string',
            'landmarks' => 'sometimes|array',
            'landmarks.*.name' => 'required|string|max:255',
            'landmarks.*.distance' => 'required|string',
            'amenities' => 'sometimes|array',
            'amenities.*.amenity_id' => 'required|exists:amenities,id',
            'images' => 'sometimes|array',
            'images.*.image_url' => 'required|file|image|mimes:jpeg,png,jpg,gif|max:1000',
            'images.*.is_main' => 'nullable|in:true,false,1,0',
            'hotel_type' => 'required_if:property_type,hotel|exists:hotel_types,id',
            'guest_house_variants' => 'required_if:property_type,guesthouse|array',
            'guest_house_variants.*.variant' => 'required|string',
            'guest_house_variants.*.price' => 'required|numeric|min:1',
            'guest_house_variants.*.currency' => 'required|in:bif,dollar',
            'guest_house_variants.*.tarification_type' => 'required|string|in:monthly,daily',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $validated = $validator->validated();

        // Update property basic details
        $property->update([
            'name' => $request->name ?? $property->name,
            'description' => $request->description ?? $property->description,
            'address' => $request->address ?? $property->address,
            'city_id' => $request->city_id ?? $property->city_id,
            'property_type' => $request->property_type ?? $property->property_type,
            'whatsapp_number1' => $request->whatsapp_number1 ?? $property->whatsapp_number1,
            'whatsapp_number2' => $request->whatsapp_number2 ?? $property->whatsapp_number2,
            'latitude' => $request->latitude ?? $property->latitude,
            'longitude' => $request->longitude ?? $property->longitude
        ]);

        // Update amenities
        if (isset($validated['amenities'])) {

            $property->amenities()->detach();

            foreach ($request->amenities as $amenityData) {
                PropertyAmenity::create([
                    'property_id' => $property->id,
                    'amenity_id' => $amenityData['amenity_id'],
                    'description' => $amenityData['description'] ?? null,
                ]);
            }
        }

        // Update rules
        if (isset($validated['rules'])) {
            $property->rules()->delete();
            foreach ($validated['rules'] as $rule) {
                $property->rules()->create(['rule_description' => $rule['rule_description']]);
            }
        }

        // Update landmarks
        if (isset($validated['landmarks'])) {
            $property->landmarks()->delete();
            foreach ($validated['landmarks'] as $landmarkData) {
                $property->landmarks()->create($landmarkData);
            }
        }

        // Update hotel type
        if ($property->property_type === 'hotel' && isset($validated['hotel_type'])) {
            $property->hotelType()->updateOrCreate(
                ['property_id' => $property->id],
                ['hotel_type_id' => $validated['hotel_type']]
            );
        }

        // Update guest house variants
        if ($property->property_type === 'guesthouse') {
            $property->guestHouseType()->delete();

            if (isset($validated['guest_house_variants'])) {
                $guestHouseType = $property->guestHouseType()->create();
                foreach ($validated['guest_house_variants'] as $variant) {
                    GuestHouseVariant::create([
                        'property_guest_house_id' => $guestHouseType->id,
                        'variant' => $variant['variant'],
                        'price' => $variant['price'],
                        'currency' => $variant['currency'],
                        'tarification_type' => $variant['tarification_type']
                    ]);
                }
            }
        }

        // Update images
        if ($request->has('images')) {
            $property->images()->delete();

            foreach ($request->images as $index => $imageData) {

                // Set the first image as main and the rest as non-main
                $isMain = ($index == 0) ? true : false;

                $imageFile = $imageData['image_url'];

                // Generate a unique filename
                $imageName = time() . '_' . uniqid() . '.' . $imageFile->getClientOriginalExtension();

                // Define the destination path
                $destinationPath = resource_path('uploads/property_images');

                // Ensure the directory exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Move the image to the destination path
                $imageFile->move($destinationPath, $imageName);


                $property->images()->create([
                    'image_url' => $imageName,
                    'is_main' => $isMain,
                ]);
            }
        }

        return $this->sendResponse(new PropertyResource($property), 'Property updated successfully.');
    }

    public function enableProperty(Request $request, $id)
    {
        $property = Property::find($id);
        if (is_null($property)) {
            return $this->sendError('Property not found.');
        }

        // Enable the specified video
        $property->is_active = 1;
        $property->save();

        return $this->sendResponse(new PropertyResource($property), 'Property enabled successfully.');
    }


    public function desableProperty(Request $request, $id)
    {
        $property = Property::find($id);
        if (is_null($property)) {
            return $this->sendError('Property not found.');
        }

        // Enable the specified video
        $property->is_active = 0;
        $property->save();

        return $this->sendResponse(new PropertyResource($property), 'Property enabled successfully.');
    }



    // Delete a hotel
    public function deleteProperty($id)
    {
        $hotel = Property::find($id);

        if (is_null($hotel)) {
            return $this->sendError('Property not found.');
        }

        $hotel->delete();

        return $this->sendResponse([], 'Property deleted successfully.');
    }

    public function listing(Request $request)
    {
        // Get parameters from the request
        $search = $request->query('search');
        $propertyType = $request->query('property_type');
        $perPage = $request->query('per_page', 10);

        // Ensure property_type is provided
        if (!$propertyType) {
            return response()->json([
                'message' => 'The property_type parameter is required. Please provide a valid property type.'
            ], 400);
        }

        // Query the properties with eager loading
        $query = Property::with([
            'images',
            'hotelType.hotelType',
            'roomtypes.plans',
            'guestHouseType.guestHouseVariants',
            'city'
        ])->where('property_type', $propertyType); // Ensure property_type is always filtered

        // Apply search filter if provided (searching by name or city)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('city', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Paginate results
        $properties = $query->paginate($perPage);

        // If no results found, return a message
        if ($properties->isEmpty()) {
            return response()->json([
                'message' => 'No properties found matching the search criteria.'
            ], 404);
        }

        // Process the properties
        $propertiesData = $properties->map(function ($property) {
            $minPrice = null;
            $currency = null;

            // Fetch hotel type if applicable
            $hotelTypeName = $property->hotelType && $property->hotelType->hotelType
                ? $property->hotelType->hotelType->type_name
                : null;

            // Process hotel properties
            if ($property->property_type === 'hotel') {
                foreach ($property->roomtypes as $roomType) {
                    foreach ($roomType->plans as $plan) {
                        if ($minPrice === null || $plan->price < $minPrice) {
                            $minPrice = $plan->price;
                            $currency = $plan->currency;
                        }
                    }
                }
            }

            // Process guesthouse properties
            elseif ($property->property_type === 'guesthouse' && $property->guestHouseType) {
                if ($property->guestHouseType->guestHouseVariants->isNotEmpty()) {
                    // Sort guest house variants by price and pick the minimum
                    $minVariant = $property->guestHouseType->guestHouseVariants->sortBy('price')->first();

                    // Set the minimum price and currency
                    $minPrice = $minVariant->price;
                    $currency = $minVariant->currency;
                } else {
                    logger('No guest house variants found for property: ' . $property->name);
                }
            }

            // Return the property data
            return [
                'id'         => $property->id,
                'name'         => $property->name,
                'city'         => $property->city->name ?? null,
                'property_type'=> $property->property_type,
                'hotel_type'   => $hotelTypeName,
                'address'      => $property->address,
                'min_price'    => $minPrice,
                'currency'     => $currency,
                'images'       => $property->images->pluck('image_url'),
            ];
        });

        // Return paginated JSON response
        return response()->json([
            'data' => $propertiesData,
            'pagination' => [
                'total'        => $properties->total(),
                'count'        => $properties->count(),
                'per_page'     => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'total_pages'  => $properties->lastPage(),
            ],
        ]);
    }

    public function selectProperty($id)
    {
        // Fetch the property with related models
        $property = Property::with([
            'city', 'images', 'rules', 'landmarks', 'roomtypes',
            'amenities', 'hotelType', 'guestHouseType', 'guestHouseVariants'
        ])->findOrFail($id);

        // Determine the type (Hotel or Guesthouse)
        $isHotel = $property->hotelType !== null;
        $isGuestHouse = $property->guestHouseType !== null;

        // Fetch 5 similar properties in the same city and type
        $similarPropertiesQuery = Property::where('city_id', $property->city_id)
            ->where('id', '!=', $property->id);

        if ($isHotel) {
            $similarPropertiesQuery->whereHas('hotelType');
        } elseif ($isGuestHouse) {
            $similarPropertiesQuery->whereHas('guestHouseType');
        }

        $similarProperties = $similarPropertiesQuery->inRandomOrder()->limit(5)->get();

        // Attach the similar properties to the main property
        $property->setRelation('similarProperties', $similarProperties);

        // Return the resource
        return new PropertyDetailsResource($property);
    }

    public function showRooms($propertyId)
    {
        // Fetch property with room types, amenities, and images using eager loading
        $property = Property::with(['roomtypes.amenities', 'roomtypes.images', 'roomtypes.plans'])->find($propertyId);

        // Check if the property exists
        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        // Return the property with its rooms, using the RoomTypeResource for formatting
        return RoomTypeResource::collection($property->roomtypes);
    }

    public function getPriceRangeByTypeAndCity(Request $request)
    {
        $propertyType = $request->query('property_type');
        $cityName = $request->query('city_name'); // Accept city_name instead of city_id

        // Validate the input
        if (!$propertyType || !$cityName) {
            return response()->json(['error' => 'property_type and city_name are required'], 400);
        }

        // Fetch the city by its name
        $city = City::where('name', $cityName)->first();

        // If the city is not found, return an error
        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $minPrice = 0;
        $maxPrice = 0;

        if ($propertyType === 'hotel') {
            // Fetch prices from RoomTypePlan for hotels
            $prices = Property::where('property_type', $propertyType)
                ->where('city_id', $city->id) // Use city ID fetched from the city name
                ->whereHas('roomtypes.plans') // Ensure room plans exist
                ->with(['roomtypes.plans'])
                ->get()
                ->flatMap(function ($property) {
                    return $property->roomtypes->flatMap(function ($roomType) {
                        return $roomType->plans->pluck('price');
                    });
                });

            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;

        } elseif ($propertyType === 'guesthouse') {
            // Fetch prices from GuestHouseVariant for guesthouses
            $prices = Property::where('property_type', $propertyType)
                ->where('city_id', $city->id) // Use city ID fetched from the city name
                ->whereHas('guestHouseType.guestHouseVariants') // Ensure variants exist
                ->with(['guestHouseType.guestHouseVariants'])
                ->get()
                ->flatMap(function ($property) {
                    return $property->guestHouseType ? $property->guestHouseType->guestHouseVariants->pluck('price') : [];
                });

            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;
        }

        return response()->json([
            'property_type' => $propertyType,
            'city_name' => $cityName, // Return the city name in the response
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);
    }


    public function getPriceRangeByPropertyName(Request $request)
    {
        $propertyType = $request->query('property_type');
        $propertyName = $request->query('property_name');

        // Validate the input
        if (!$propertyType || !$propertyName) {
            return response()->json(['error' => 'property_type and property_name are required'], 400);
        }

        $minPrice = 0;
        $maxPrice = 0;

        if ($propertyType === 'hotel') {
            // Fetch prices from RoomTypePlan for hotels
            $prices = Property::where('property_type', $propertyType)
                ->where('name', 'LIKE', "%{$propertyName}%") // Search by property name
                ->whereHas('roomtypes.plans') // Ensure room plans exist
                ->with(['roomtypes.plans'])
                ->get()
                ->flatMap(function ($property) {
                    return $property->roomtypes->flatMap(function ($roomType) {
                        return $roomType->plans->pluck('price');
                    });
                });

            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;

        } elseif ($propertyType === 'guesthouse') {
            // Fetch prices from GuestHouseVariant for guesthouses
            $prices = Property::where('property_type', $propertyType)
                ->where('name', 'LIKE', "%{$propertyName}%") // Search by property name
                ->whereHas('guestHouseType.guestHouseVariants') // Ensure variants exist
                ->with(['guestHouseType.guestHouseVariants'])
                ->get()
                ->flatMap(function ($property) {
                    return $property->guestHouseType ? $property->guestHouseType->guestHouseVariants->pluck('price') : [];
                });

            $minPrice = $prices->min() ?? 0;
            $maxPrice = $prices->max() ?? 0;
        }

        return response()->json([
            'property_type' => $propertyType,
            'property_name' => $propertyName,
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);
    }

    public function getGuestHouseDetails($id)
    {
        $property = Property::with([
            'roomtypes.images',       // Eager load room images
            'roomtypes.amenities',    // Eager load room amenities
            'guestHouseVariants'      // Eager load guest house variants
        ])
            ->where('id', $id)
            ->where('property_type', 'guesthouse')
            ->first();

        if (!$property) {
            return response()->json(['message' => 'Property not found'], 404);
        }

        // Return property with room types, images, amenities, and guest house variants
        return response()->json($property, 200);
    }

    public function filterProperties(Request $request)
    {
        // Get parameters from the request
        $search = $request->query('search');
        $propertyType = $request->query('property_type');
        $perPage = $request->query('per_page', 10);
        $amenities = $request->query('amenities', []); // Array of amenity IDs
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $sortBy = $request->query('sort_by'); // "price_low_to_high" or "price_high_to_low"

        // Ensure property_type is provided
        if (!$propertyType) {
            return response()->json([
                'message' => 'The property_type parameter is required. Please provide a valid property type.'
            ], 400);
        }

        // Query the properties with eager loading
        $query = Property::with([
            'images',
            'hotelType.hotelType',
            'roomtypes.plans',
            'guestHouseType.guestHouseVariants',
            'city',
            'amenities' // Load amenities
        ])->where('property_type', $propertyType);

        // Apply search filter if provided (searching by name or city)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('city', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filter by selected amenities
        if (!empty($amenities)) {
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.id', $amenities);
            });
        }

        // Filter by price range
        if ($minPrice !== null || $maxPrice !== null) {
            $query->whereHas('roomtypes.plans', function ($q) use ($minPrice, $maxPrice) {
                if ($minPrice !== null) {
                    $q->where('price', '>=', $minPrice);
                }
                if ($maxPrice !== null) {
                    $q->where('price', '<=', $maxPrice);
                }
            });
        }

        // Paginate results
        $properties = $query->paginate($perPage);

        // If no results found, return a message
        if ($properties->isEmpty()) {
            return response()->json([
                'message' => 'No properties found matching the search criteria.'
            ], 404);
        }

        // Process the properties and calculate min_price
        $propertiesData = $properties->map(function ($property) {
            $minPrice = null;
            $currency = null;

            // Fetch hotel type if applicable
            $hotelTypeName = $property->hotelType && $property->hotelType->hotelType
                ? $property->hotelType->hotelType->type_name
                : null;

            // Process hotel properties
            if ($property->property_type === 'hotel') {
                foreach ($property->roomtypes as $roomType) {
                    foreach ($roomType->plans as $plan) {
                        if ($minPrice === null || $plan->price < $minPrice) {
                            $minPrice = $plan->price;
                            $currency = $plan->currency;
                        }
                    }
                }
            }
            // Process guesthouse properties
            elseif ($property->property_type === 'guesthouse' && $property->guestHouseType) {
                if ($property->guestHouseType->guestHouseVariants->isNotEmpty()) {
                    $minVariant = $property->guestHouseType->guestHouseVariants->sortBy('price')->first();
                    $minPrice = $minVariant->price;
                    $currency = $minVariant->currency;
                }
            }

            return [
                'id'           => $property->id,
                'name'         => $property->name,
                'city'         => $property->city->name ?? null,
                'property_type'=> $property->property_type,
                'hotel_type'   => $hotelTypeName,
                'address'      => $property->address,
                'min_price'    => $minPrice,
                'currency'     => $currency,
                'images'       => $property->images->pluck('image_url'),
            ];
        });

        // Apply sorting if needed
        if ($sortBy === 'price_low_to_high') {
            $propertiesData = $propertiesData->sortBy('min_price')->values();
        } elseif ($sortBy === 'price_high_to_low') {
            $propertiesData = $propertiesData->sortByDesc('min_price')->values();
        }

        // Return paginated JSON response
        return response()->json([
            'data' => $propertiesData,
            'pagination' => [
                'total'        => $properties->total(),
                'count'        => $properties->count(),
                'per_page'     => $properties->perPage(),
                'current_page' => $properties->currentPage(),
                'total_pages'  => $properties->lastPage(),
            ],
        ]);
    }

}

