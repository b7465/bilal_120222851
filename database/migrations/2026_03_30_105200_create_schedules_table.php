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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('course_code')->nullable();
            $table->string('course_name')->nullable();
            $table->string('instructor')->nullable();
            $table->string('days')->nullable();
            $table->string('time')->nullable();
            $table->string('room')->nullable();
            $table->json('raw_data')->nullable(); // To store the raw JSON from the API in case structure varies
            $table->timestamps();

            // Setup a foreign key relationship if desirable, although not strictly needed if student_id is just an identifier here.
            $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
