<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->foreignId('room_plan_id')
                ->nullable()
                ->constrained('room_type_plans')
                ->onDelete('cascade');
        });
    }


    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropForeign(['room_plan_id']);
            $table->dropColumn('room_plan_id');
        });
    }
};
