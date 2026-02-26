<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBreakTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('break_times', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
        $table->timestamp('break_start');
        $table->timestamp('break_end')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('break_times');
    }
}
