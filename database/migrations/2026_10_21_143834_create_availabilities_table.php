<?php

use App\Enums\AvailabilityDefType;
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
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctor_profiles');
            $table->enum('type', [AvailabilityDefType::ONCE, AvailabilityDefType::RECURRING]);
            $table->string('rrule')->nullable();
            $table->dateTimeTz('date')->nullable();
            $table->timeTz('shift_start_at');
            $table->timeTz('shift_end_at');
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('availabilities');
    }
};
