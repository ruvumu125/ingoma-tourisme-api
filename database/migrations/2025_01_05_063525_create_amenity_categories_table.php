<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmenityCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('amenity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->enum('type', ['hotel','guesthouse','hotel and guesthouse', 'room','all']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('amenity_categories');
    }
}
