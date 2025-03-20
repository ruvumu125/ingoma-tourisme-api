<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\GuestDetail;
use App\Models\GuestHouseBooking;
use App\Models\HotelBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends BaseController
{
    public function saveBooking(Request $request)
    {
        $validator  = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'property_id'   => 'required|exists:properties,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'unit_price'    => 'required|numeric|min:0',
            'pricing_type'  => 'required|in:daily,monthly',
            'duration'      => 'required|numeric|min:1',
            'currency'      => 'required|in:bif,dollar',
            'booking_type'  => 'required|in:hotel,guest_house',

            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'phone'  => 'required|string|max:20',
            'email'         => 'required|email|max:255',

            // Conditional validation for hotel booking
            'adults'        => 'required_if:booking_type,hotel|numeric|min:1',
            'children'      => 'required_if:booking_type,hotel|numeric|min:0',
            'room_id'       => 'required_if:booking_type,hotel|exists:room_types,id',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        DB::beginTransaction();
        try {
            // Calculate total price
            $total_price = $request->unit_price * $request->duration;
            // Generate unique booking number
            $bookingNumber = 'BOOK-' . strtoupper(Str::random(8));

            // Create Booking
            $booking = Booking::create([
                'booking_number' => $bookingNumber,
                'user_id'        => $request->user_id, // Authenticated user
                'property_id'    => $request->property_id,
                'check_in_date'  => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'booking_date'   => now(),
                'unit_price'     => $request->unit_price,
                'pricing_type'   => $request->pricing_type,
                'duration'       => $request->duration,
                'total_price'    => $total_price,
                'currency'       => $request->currency,
                'booking_type'   => $request->booking_type,
                'status'         => 'pending',
            ]);

            GuestDetail::create([
                'booking_id'   => $booking->id,
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);


            // Store Hotel Booking or Guest House Booking
            if ($request->booking_type === 'hotel') {

                HotelBooking::create([
                    'booking_id' => $booking->id,
                    'adults'     => $request->adults,
                    'children'   => $request->children,
                    'room_id'    => $request->room_id,
                ]);
            } else {
                GuestHouseBooking::create([
                    'booking_id' => $booking->id,
                ]);
            }



            DB::commit();
            return response()->json([
                'message' => 'Booking successful!',
                'booking' => $booking,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Booking failed!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserHotelBookings($userId)
    {
        // Fetch user bookings where booking type is hotel, with related property and images, with pagination and ordering by booking_date
        $bookings = Booking::with([
            'property',
            'property.images',
            'property.city',
            'hotelBooking' // Hotel-specific data
        ])
            ->where('user_id', $userId)
            ->where('booking_type', 'hotel')  // Filter by booking type
            ->orderBy('booking_date', 'desc') // Order by booking date, descending (current bookings first)
            ->paginate(10);  // Adjust the number of items per page as needed

        // Map the bookings and format the response
        $response = $bookings->map(function ($booking) {
            // Find the main image (where 'is_main' is true)
            $mainImage = $booking->property->images->firstWhere('is_main', true);

            return [
                'booking_id'     => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'check_in_date'  => $booking->check_in_date,
                'check_out_date' => $booking->check_out_date,
                'pricing_type'   => $booking->pricing_type,
                'duration'       => $booking->duration,
                'property' => [
                    'property_id'      => $booking->property_id,
                    'property_name'    => $booking->property->name,
                    'property_address' => $booking->property->address,
                    'property_main_image' => $mainImage ? $mainImage->image_url : null,
                ]
            ];
        });

        // Return the paginated response with custom pagination format
        return response()->json([
            'data' => $response,
            'pagination' => [
                'total' => $bookings->total(),
                'count' => $bookings->count(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'total_pages' => $bookings->lastPage(),
            ],
        ]);
    }

    public function getUserGuestHouseBookings($userId)
    {
        // Fetch user bookings where booking type is guesthouse, with related property and images, with pagination and ordering by booking_date
        $bookings = Booking::with([
            'property',
            'property.images',
            'property.city',
            'guestHouseBooking' // Guesthouse-specific data
        ])
            ->where('user_id', $userId)
            ->where('booking_type', 'guest_house')  // Filter by booking type
            ->orderBy('booking_date', 'desc') // Order by booking date, descending (current bookings first)
            ->paginate(10);  // Adjust the number of items per page as needed

        // Map the bookings and format the response
        $response = $bookings->map(function ($booking) {
            // Find the main image (where 'is_main' is true)
            $mainImage = $booking->property->images->firstWhere('is_main', true);

            return [
                'booking_id'     => $booking->id,
                'booking_number' => $booking->booking_number,
                'status' => $booking->status,
                'check_in_date'  => $booking->check_in_date,
                'check_out_date' => $booking->check_out_date,
                'pricing_type'   => $booking->pricing_type,
                'duration'       => $booking->duration,
                'property' => [
                    'property_id'      => $booking->property_id,
                    'property_name'    => $booking->property->name,
                    'property_address' => $booking->property->address,
                    'property_main_image' => $mainImage ? $mainImage->image_url : null,
                ]
            ];
        });

        // Return the paginated response with custom pagination format
        return response()->json([
            'data' => $response,
            'pagination' => [
                'total' => $bookings->total(),
                'count' => $bookings->count(),
                'per_page' => $bookings->perPage(),
                'current_page' => $bookings->currentPage(),
                'total_pages' => $bookings->lastPage(),
            ],
        ]);
    }

    public function getBookingDetails($bookingId)
    {
        // Fetch the booking with all necessary relationships
        $booking = Booking::with([
            'property',
            'property.images', // Property images
            'guestDetail', // Guest details
            'payment', // Payment info
            'hotelBooking.room', // Room details from HotelBooking
            'hotelBooking.room.images', // Room images
            'hotelBooking.room.amenities.category' // Room amenities with categories
        ])
            ->where('id', $bookingId)
            ->first();

        // Check if the booking exists
        if (!$booking) {
            return response()->json(['message' => 'Booking not found'], 404);
        }

        // Find the main image of the property (if it exists)
        $propertyMainImage = $booking->property->images->firstWhere('is_main', true);

        // Initialize room details (if booking type is "hotel")
        $roomDetails = null;

        if ($booking->booking_type === 'hotel' && $booking->hotelBooking) {
            $room = $booking->hotelBooking->room;
            if ($room) {
                $roomMainImage = $room->images->firstWhere('is_main', true);
                $roomAmenities = $room->amenities->map(function ($amenity) {
                    return [
                        'name'         => $amenity->name,
                        'category'     => $amenity->category ? $amenity->category->name : 'Uncategorized',
                        'description'  => $amenity->pivot->description ?? 'No description',
                    ];
                });

                $roomDetails = [
                    'room_type_name'  => $room->type_name,
                    'room_description'=> $room->description,
                    'room_main_image' => $roomMainImage ? $roomMainImage->image_url : null,
                    'room_amenities'  => $roomAmenities, // ✅ Moved here
                ];
            }
        }

        // Guest Details
        $guestDetails = $booking->guestDetail
            ? [
                'first_name' => $booking->guestDetail->first_name ?? 'N/A',
                'last_name'  => $booking->guestDetail->last_name ?? 'N/A',
                'email'      => $booking->guestDetail->email ?? 'N/A',
                'phone'      => $booking->guestDetail->phone ?? 'N/A',
            ]
            : null;

        // Prepare the response data
        $response = [
            'booking_details' => [
                'booking_id'      => $booking->id,
                'booking_number'  => $booking->booking_number,
                'booking_date'    => $booking->booking_date,
                'check_in_date'   => $booking->check_in_date,
                'check_out_date'  => $booking->check_out_date,
                'duration'        => $booking->duration,
                'unit_price'      => $booking->unit_price,
                'total_price'     => $booking->total_price,
                'currency'        => $booking->currency,
                'pricing_type'    => $booking->pricing_type,
                'booking_type'    => $booking->booking_type,
                'status'          => $booking->status,
                'adults'          => $booking->hotelBooking ? $booking->hotelBooking->adults : null, // ✅ Moved here
                'children'        => $booking->hotelBooking ? $booking->hotelBooking->children : null, // ✅ Moved here
            ],
            'property_details' => [
                'property_id'     => $booking->property->id,
                'property_name'   => $booking->property->name,
                'property_address'=> $booking->property->address,
                'property_main_image' => $propertyMainImage ? $propertyMainImage->image_url : null,
            ],
            'room_details' => $roomDetails,
            'guest_details' => $guestDetails,
            'payment_info' => $booking->payment ? [
                'payment_status'  => $booking->payment->status ?? 'Unpaid',
                'payment_amount'  => $booking->payment->amount ?? 0,
                'payment_method'  => $booking->payment->method ?? 'N/A',
            ] : null,
        ];

        // Return the response
        return response()->json($response);
    }



















}
