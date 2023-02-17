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
        Schema::create('alumno_trabajos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tt_id');
            $table->foreign('tt_id')->references('id')->on('trabajos');
            $table->string('student_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumno_trabajos');
    }
};
