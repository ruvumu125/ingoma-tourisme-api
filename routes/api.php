<?php

use App\Http\Controllers\API\AdvertisementController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\DestinationSearchController;
use App\Http\Controllers\API\PropertyController;
use App\Http\Controllers\API\HotelTypeController;
use App\Http\Controllers\API\RoomTypeController;
use App\Http\Controllers\API\AmenityCategoryController;
use App\Http\Controllers\API\AmenityController;
use App\Http\Controllers\API\UserController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::group(['prefix' => 'v1'], function () {

    Route::post('/login', [UserController::class, 'login']);
    Route::post('/customerRegister', [UserController::class, 'customerRegister']);
    Route::post('/administratorRegister', [UserController::class, 'adminRegister']);
    Route::post('/superAdministratorRegister', [UserController::class, 'superAdminRegister']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
    Route::put('/enableUser/{id}', [UserController::class, 'enableUser']);
    Route::put('/desableUser/{id}', [UserController::class, 'desableUser']);
    Route::delete('/users/{id}', [UserController::class, 'deleteUser']);

    Route::get('/super-admin-pagination-search', [UserController::class, 'getAllSuperAdminsPaginationSearch']);
    Route::get('/admin-pagination-search', [UserController::class, 'getAllAdminsPaginationSearch']);
    Route::get('/customer-pagination-search', [UserController::class, 'getAllCustomersPaginationSearch']);


    Route::middleware(['auth:sanctum', RoleMiddleware::class . ':customer'])->group(function () {

        Route::post('/saveBooking', [BookingController::class, 'saveBooking']);
        Route::get('/hotel-bookings/user/{userId}', [BookingController::class, 'getUserHotelBookings']);
        Route::get('/gueshouse-bookings/user/{userId}', [BookingController::class, 'getUserGuestHouseBookings']);
        Route::get('/booking-details/{bookingId}', [BookingController::class, 'getBookingDetails']);
    });
//    });


    //bookings
    Route::get('/bookings', [BookingController::class, 'getAllBookings']);
    Route::get('/bookings/pending', [BookingController::class, 'getPendingBookings']);
    Route::get('/bookings/confirmed', [BookingController::class, 'getConfirmedBookings']);
    Route::get('/bookings/cancelled', [BookingController::class, 'getCancelledBookings']);
    Route::get('/bookings/paid', [BookingController::class, 'getPaidBookings']);
    Route::get('/booking/details/{id}', [BookingController::class, 'getDetailsBooking']);
    Route::put('/bookings/confirm/{id}', [BookingController::class, 'confirmBooking']);
    Route::put('/bookings/cancel/{id}', [BookingController::class, 'cancelBooking']);

    Route::get('/advertisements', [AdvertisementController::class, 'getAllAdvertisements']);
    Route::get('/advertisement/{id}', [AdvertisementController::class, 'getAdvertisementById']);
    Route::post('/advertisement', [AdvertisementController::class, 'addAdvertisement']);
    Route::post('/advertisements/{id}', [AdvertisementController::class, 'updateAdvertisement']);

    Route::put('/enableAdvertisement/{id}', [AdvertisementController::class, 'enableAdvertisement']);
    Route::put('/desableAdvertisement/{id}', [AdvertisementController::class, 'desableAdvertisement']);
    Route::delete('/advertisements/{id}', [AdvertisementController::class, 'deleteAdvertisement']);
    Route::get('/advertisements/active', [AdvertisementController::class, 'getActiveAdvertisement']);








    //destination search
    Route::get('destination-search', [DestinationSearchController::class, 'search']);

    // List all cities without pagination
    Route::get('cities/list-all', [CityController::class, 'listAllCities']);

    // List all cities with pagination and search
    Route::get('cities', [CityController::class, 'getAllCities']);

    // Add a new city
    Route::post('cities', [CityController::class, 'addCity']);

    // Get a city by ID
    Route::get('cities/{id}', [CityController::class, 'getCityById']);

    // Update a city
    Route::put('cities/{id}', [CityController::class, 'updateCity']);

    // Delete a city
    Route::delete('cities/{id}', [CityController::class, 'deleteCity']);



    // List all hotel types without pagination
    Route::get('hotel-types/list-all', [HotelTypeController::class, 'listAllHotelTypes']);

    // List all hotel types with pagination and search
    Route::get('hotel-types', [HotelTypeController::class, 'getAllHotelTypes']);

    // Add a new hotel type
    Route::post('hotel-types', [HotelTypeController::class, 'addHotelType']);

    // Get a hotel type by ID
    Route::get('hotel-types/{id}', [HotelTypeController::class, 'getHotelTypeById']);

    // Update a hotel type
    Route::put('hotel-types/{id}', [HotelTypeController::class, 'updateHotelType']);

    // Delete a hotel type
    Route::delete('hotel-types/{id}', [HotelTypeController::class, 'deleteHotelType']);


    Route::get('/amenity-categories', [AmenityCategoryController::class, 'listAllAmenityCategories']);
    Route::get('/amenity-categories/paginate', [AmenityCategoryController::class, 'getAllAmenityCategories']);
    Route::post('/amenity-categories', [AmenityCategoryController::class, 'addAmenityCategory']);
    Route::get('/amenity-categories/{id}', [AmenityCategoryController::class, 'getAmenityCategoryById']);
    Route::put('/amenity-categories/{id}', [AmenityCategoryController::class, 'updateAmenityCategory']);
    Route::delete('/amenity-categories/{id}', [AmenityCategoryController::class, 'deleteAmenityCategory']);

    Route::get('/amenities-by-property-type-and-city', [AmenityController::class, 'getAmenitiesByTypeAndCity']);
    Route::get('/amenities-by-property-type-and-propertyname', [AmenityController::class, 'getAmenitiesByTypeAndPropertyName']);

    Route::get('/amenities', [AmenityController::class, 'listAllAmenities']);
    Route::get('/property-amenities', [AmenityController::class, 'propertyAmenitiesList']);
    Route::get('/room-amenities', [AmenityController::class, 'roomAmenitiesList']);
    Route::get('/amenities/paginate', [AmenityController::class, 'getAllAmenities']);
    Route::post('/amenities', [AmenityController::class, 'addAmenity']);
    Route::get('/amenities/{id}', [AmenityController::class, 'getAmenityById']);
    Route::put('/amenities/{id}', [AmenityController::class, 'updateAmenity']);
    Route::delete('/amenities/{id}', [AmenityController::class, 'deleteAmenity']);

    Route::get('/properties/filter', [PropertyController::class, 'filterProperties']);
    Route::get('/guesthouse-rooms/{id}', [PropertyController::class, 'getGuestHouseDetails']);
    Route::get('/price-range-by-property-type-and-city', [PropertyController::class, 'getPriceRangeByTypeAndCity']);
    Route::get('/price-range-by-property-type-and-propertyname', [PropertyController::class, 'getPriceRangeByPropertyName']);
    Route::get('/properties-listing', [PropertyController::class, 'listing']);
    Route::get('/select-property/{id}', [PropertyController::class, 'selectProperty']);
    Route::get('/properties', [PropertyController::class, 'listAllProperties']);
    Route::get('/properties/paginate', [PropertyController::class, 'getAllProperties']);
    Route::get('/properties/{id}', [PropertyController::class, 'getPropertyById']);
    Route::get('/property-details/{id}', [PropertyController::class, 'getPropertyDetails']);
    Route::post('/properties', [PropertyController::class, 'addProperty']);
    Route::post('/properties/{id}', [PropertyController::class, 'updateProperty']);
    Route::delete('/properties/{id}', [PropertyController::class, 'deleteProperty']);
    Route::put('/enableProperty/{id}', [PropertyController::class, 'enableProperty']);
    Route::put('/desableProperty/{id}', [PropertyController::class, 'desableProperty']);

    Route::get('/show-rooms/{propertyId}', [PropertyController::class, 'showRooms']);
    Route::get('room-types', [RoomTypeController::class, 'listAllRoomTypes']);
    Route::get('room-types/paginate', [RoomTypeController::class, 'getAllRoomTypes']);
    Route::post('room-types', [RoomTypeController::class, 'addRoomType']);
    Route::get('room-types/{id}', [RoomTypeController::class, 'getRoomTypeById']);
    Route::get('/room-details/{id}', [RoomTypeController::class, 'getRoomTypeDetails']);
    Route::post('room-types/{id}', [RoomTypeController::class, 'updateRoomType']);
    Route::delete('room-types/{id}', [RoomTypeController::class, 'deleteRoomType']);

    //Récupérer une photo d'un logement
    Route::get('/property-image/{filename}', function ($filename) {
        // Path to the image in the resources/uploads/property_images directory
        $path = resource_path('uploads/property_images/' . $filename);

        // Check if the file exists
        if (!File::exists($path)) {
            abort(404, "Image not found.");
        }

        // Get the file's content
        $file = File::get($path);

        // Get the MIME type of the file
        $mimeType = File::mimeType($path);

        // Return the file with the appropriate content type
        return response($file, 200)->header("Content-Type", $mimeType);
    });

    //Récupérer une photo d'une chambre
    Route::get('/room-image/{filename}', function ($filename) {
        // Path to the image in the resources/uploads/property_images directory
        $path = resource_path('uploads/room_images/' . $filename);

        // Check if the file exists
        if (!File::exists($path)) {
            abort(404, "Image not found.");
        }

        // Get the file's content
        $file = File::get($path);

        // Get the MIME type of the file
        $mimeType = File::mimeType($path);

        // Return the file with the appropriate content type
        return response($file, 200)->header("Content-Type", $mimeType);
    });

    //Récupérer une photo de la publicite
    Route::get('/advertisement-image/{filename}', function ($filename) {
        // Path to the image in the resources/uploads/property_images directory
        $path = resource_path('uploads/advertisement_images/' . $filename);

        // Check if the file exists
        if (!File::exists($path)) {
            abort(404, "Image not found.");
        }

        // Get the file's content
        $file = File::get($path);

        // Get the MIME type of the file
        $mimeType = File::mimeType($path);

        // Return the file with the appropriate content type
        return response($file, 200)->header("Content-Type", $mimeType);
    });





});
