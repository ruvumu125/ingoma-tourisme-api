<?php

use App\Http\Controllers\API\AdvertisementController;
use App\Http\Controllers\API\BookingController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\DestinationSearchController;
use App\Http\Controllers\API\NotificationController;
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
    Route::get('/advertisements/active', [AdvertisementController::class, 'getActiveAdvertisement']);

    Route::get('destination-search', [DestinationSearchController::class, 'search']);
    Route::get('/amenities-by-property-type-and-city', [AmenityController::class, 'getAmenitiesByTypeAndCity']);
    Route::get('/amenities-by-property-type-and-propertyname', [AmenityController::class, 'getAmenitiesByTypeAndPropertyName']);

    Route::get('/properties/filter', [PropertyController::class, 'filterProperties']);
    Route::get('/guesthouse-rooms/{id}', [PropertyController::class, 'getGuestHouseDetails']);
    Route::get('/price-range-by-property-type-and-city', [PropertyController::class, 'getPriceRangeByTypeAndCity']);
    Route::get('/price-range-by-property-type-and-propertyname', [PropertyController::class, 'getPriceRangeByPropertyName']);
    Route::get('/properties-listing', [PropertyController::class, 'listing']);
    Route::get('/select-property/{id}', [PropertyController::class, 'selectProperty']);
    Route::get('/show-rooms/{propertyId}', [PropertyController::class, 'showRooms']);


    Route::get('/notifications/user/{userId}', [NotificationController::class, 'getUserNotifications']); // Fetch notifications for a user
    Route::post('/notifications', [NotificationController::class, 'addNotification']);


    Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superadmin'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'getCounts']);
        Route::post('/administratorRegister', [UserController::class, 'adminRegister']);
        Route::post('/superAdministratorRegister', [UserController::class, 'superAdminRegister']);
        Route::put('/users/{id}', [UserController::class, 'updateUser']);
        Route::put('/enableUser/{id}', [UserController::class, 'enableUser']);
        Route::put('/desableUser/{id}', [UserController::class, 'desableUser']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);

        Route::get('/super-admin-pagination-search', [UserController::class, 'getAllSuperAdminsPaginationSearch']);
        Route::get('/admin-pagination-search', [UserController::class, 'getAllAdminsPaginationSearch']);

    });

    Route::middleware(['auth:sanctum', RoleMiddleware::class . ':superadmin,admin,customer'])->group(function () {
        Route::get('/booking/details/{id}', [BookingController::class, 'getDetailsBooking']);
    });

    Route::middleware(['auth:sanctum', RoleMiddleware::class . ':admin,superadmin'])->group(function () {

        Route::get('/advertisements', [AdvertisementController::class, 'getAllAdvertisements']);
        Route::get('/advertisement/{id}', [AdvertisementController::class, 'getAdvertisementById']);
        Route::post('/advertisement', [AdvertisementController::class, 'addAdvertisement']);
        Route::post('/advertisements/{id}', [AdvertisementController::class, 'updateAdvertisement']);
        Route::put('/enableAdvertisement/{id}', [AdvertisementController::class, 'enableAdvertisement']);
        Route::put('/desableAdvertisement/{id}', [AdvertisementController::class, 'desableAdvertisement']);
        Route::delete('/advertisements/{id}', [AdvertisementController::class, 'deleteAdvertisement']);

        //bookings
        Route::get('/bookings', [BookingController::class, 'getAllBookings']);
        Route::get('/bookings/pending', [BookingController::class, 'getPendingBookings']);
        Route::get('/bookings/confirmed', [BookingController::class, 'getConfirmedBookings']);
        Route::get('/bookings/cancelled', [BookingController::class, 'getCancelledBookings']);
        Route::get('/bookings/paid', [BookingController::class, 'getPaidBookings']);
        Route::put('/bookings/confirm/{id}', [BookingController::class, 'confirmBooking']);
        Route::put('/bookings/cancel/{id}', [BookingController::class, 'cancelBooking']);

        //cities
        Route::get('cities/list-all', [CityController::class, 'listAllCities']);
        Route::get('cities', [CityController::class, 'getAllCities']);
        Route::post('cities', [CityController::class, 'addCity']);
        Route::get('cities/{id}', [CityController::class, 'getCityById']);
        Route::put('cities/{id}', [CityController::class, 'updateCity']);
        Route::delete('cities/{id}', [CityController::class, 'deleteCity']);

        // List all hotel types without pagination
        Route::get('hotel-types/list-all', [HotelTypeController::class, 'listAllHotelTypes']);
        Route::get('hotel-types', [HotelTypeController::class, 'getAllHotelTypes']);
        Route::post('hotel-types', [HotelTypeController::class, 'addHotelType']);
        Route::get('hotel-types/{id}', [HotelTypeController::class, 'getHotelTypeById']);
        Route::put('hotel-types/{id}', [HotelTypeController::class, 'updateHotelType']);
        Route::delete('hotel-types/{id}', [HotelTypeController::class, 'deleteHotelType']);


        Route::get('/amenity-categories', [AmenityCategoryController::class, 'listAllAmenityCategories']);
        Route::get('/amenity-categories/paginate', [AmenityCategoryController::class, 'getAllAmenityCategories']);
        Route::post('/amenity-categories', [AmenityCategoryController::class, 'addAmenityCategory']);
        Route::get('/amenity-categories/{id}', [AmenityCategoryController::class, 'getAmenityCategoryById']);
        Route::put('/amenity-categories/{id}', [AmenityCategoryController::class, 'updateAmenityCategory']);
        Route::delete('/amenity-categories/{id}', [AmenityCategoryController::class, 'deleteAmenityCategory']);

        Route::get('/amenities', [AmenityController::class, 'listAllAmenities']);
        Route::get('/property-amenities', [AmenityController::class, 'propertyAmenitiesList']);
        Route::get('/room-amenities', [AmenityController::class, 'roomAmenitiesList']);
        Route::get('/amenities/paginate', [AmenityController::class, 'getAllAmenities']);
        Route::post('/amenities', [AmenityController::class, 'addAmenity']);
        Route::get('/amenities/{id}', [AmenityController::class, 'getAmenityById']);
        Route::put('/amenities/{id}', [AmenityController::class, 'updateAmenity']);
        Route::delete('/amenities/{id}', [AmenityController::class, 'deleteAmenity']);

        Route::get('/properties', [PropertyController::class, 'listAllProperties']);
        Route::get('/properties/paginate', [PropertyController::class, 'getAllProperties']);
        Route::get('/properties/{id}', [PropertyController::class, 'getPropertyById']);
        Route::get('/property-details/{id}', [PropertyController::class, 'getPropertyDetails']);
        Route::post('/properties', [PropertyController::class, 'addProperty']);
        Route::post('/properties/{id}', [PropertyController::class, 'updateProperty']);
        Route::delete('/properties/{id}', [PropertyController::class, 'deleteProperty']);
        Route::put('/enableProperty/{id}', [PropertyController::class, 'enableProperty']);
        Route::put('/desableProperty/{id}', [PropertyController::class, 'desableProperty']);

        Route::get('room-types', [RoomTypeController::class, 'listAllRoomTypes']);
        Route::get('room-types/paginate', [RoomTypeController::class, 'getAllRoomTypes']);
        Route::post('room-types', [RoomTypeController::class, 'addRoomType']);
        Route::get('room-types/{id}', [RoomTypeController::class, 'getRoomTypeById']);
        Route::get('/room-details/{id}', [RoomTypeController::class, 'getRoomTypeDetails']);
        Route::post('room-types/{id}', [RoomTypeController::class, 'updateRoomType']);
        Route::delete('room-types/{id}', [RoomTypeController::class, 'deleteRoomType']);

        Route::get('/customer-pagination-search', [UserController::class, 'getAllCustomersPaginationSearch']);


    });

    Route::middleware(['auth:sanctum', RoleMiddleware::class . ':customer'])->group(function () {

        Route::post('/saveBooking', [BookingController::class, 'saveBooking']);
        Route::get('/hotel-bookings/user/{userId}', [BookingController::class, 'getUserHotelBookings']);
        Route::get('/gueshouse-bookings/user/{userId}', [BookingController::class, 'getUserGuestHouseBookings']);
    });



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
