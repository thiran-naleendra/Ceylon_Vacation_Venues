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
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('destination')->nullable()->after('description');
            $table->string('availability_status', 30)->default('available')->after('destination');
            $table->index(['availability_status', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropIndex(['availability_status', 'status']);
            $table->dropColumn(['destination', 'availability_status']);
        });
    }
};
