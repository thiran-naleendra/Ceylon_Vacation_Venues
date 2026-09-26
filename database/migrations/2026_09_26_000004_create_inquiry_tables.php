<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->char('reference', 26)->unique();
            $table->string('type', 20);
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 40)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('status', 30)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('privacy_notice_version', 50)->nullable();
            $table->timestamp('privacy_acknowledged_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->index(['type', 'status', 'created_at']);
            $table->index(['assigned_to', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
            $table->timestamps();
        });

        Schema::create('package_inquiry_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->unique()->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->restrictOnDelete();
            $table->string('package_title_snapshot');
            $table->date('preferred_start_date')->nullable();
            $table->date('preferred_end_date')->nullable();
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->timestamps();
        });

        Schema::create('rental_inquiry_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->unique()->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->restrictOnDelete();
            $table->foreignId('vehicle_category_id')->nullable()->constrained('vehicle_categories')->restrictOnDelete();
            $table->string('vehicle_title_snapshot')->nullable();
            $table->dateTime('pickup_at')->nullable()->index();
            $table->dateTime('return_at')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('return_location')->nullable();
            $table->boolean('driver_required')->nullable();
            $table->timestamps();
        });

        Schema::create('visa_inquiry_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->unique()->constrained('inquiries')->cascadeOnDelete();
            $table->char('nationality_code', 2)->nullable();
            $table->date('arrival_date')->nullable();
            $table->date('current_visa_expiry_date')->nullable()->index();
            $table->unsignedSmallInteger('requested_extension_days')->nullable();
            $table->timestamps();
        });

        Schema::create('baggage_inquiry_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->unique()->constrained('inquiries')->cascadeOnDelete();
            $table->string('pickup_location');
            $table->string('delivery_location');
            $table->dateTime('pickup_at')->nullable()->index();
            $table->unsignedSmallInteger('bag_count');
            $table->decimal('estimated_weight_kg', 8, 2)->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });

        Schema::create('inquiry_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->index(['inquiry_id', 'created_at']);
            $table->timestamps();
        });

        Schema::create('inquiry_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inquiry_id')->constrained('inquiries')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('reason')->nullable();
            $table->index(['inquiry_id', 'created_at']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiry_status_histories');
        Schema::dropIfExists('inquiry_notes');
        Schema::dropIfExists('baggage_inquiry_details');
        Schema::dropIfExists('visa_inquiry_details');
        Schema::dropIfExists('rental_inquiry_details');
        Schema::dropIfExists('package_inquiry_details');
        Schema::dropIfExists('inquiries');
    }
};
