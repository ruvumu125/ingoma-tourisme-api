<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guest_house_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_guest_house_id')->constrained('property_guest_house_types')->onDelete('cascade');
            $table->string('variant'); // 'per_night', 'per_week', 'per_month', etc.
            $table->decimal('price', 10, 2); // The price value
            $table->enum('currency', ['bif', 'dollar']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_rental_prices');
    }
};
