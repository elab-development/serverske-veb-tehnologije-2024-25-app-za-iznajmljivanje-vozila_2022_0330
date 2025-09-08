<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('model');
            $table->string('registration_number')->unique();
            $table->unsignedInteger('year');
            $table->unsignedInteger('daily_price');
            $table->string('color')->nullable();
            $table->unsignedInteger('mileage')->default(0);
            $table->enum('fuel_type', ['dizel', 'benzin', 'električni', 'hibrid']);
            $table->enum('transmission', ['manuelni', 'automatski']);
            $table->unsignedInteger('seats');
            $table->unsignedInteger('doors');
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle');
    }
};
