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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('model_year');
            $table->unsignedSmallInteger('luggage_capacity')->nullable()->after('seats');
            $table->boolean('has_air_conditioning')->default(false)->after('luggage_capacity');
            $table->string('availability_status', 30)->default('available')->after('rental_terms');
            $table->index(['availability_status', 'vehicle_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropIndex(['availability_status', 'vehicle_category_id']);
            $table->dropColumn(['summary', 'luggage_capacity', 'has_air_conditioning', 'availability_status']);
        });
    }
};
