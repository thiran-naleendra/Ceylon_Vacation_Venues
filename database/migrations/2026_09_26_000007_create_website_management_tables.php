<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path', 512)->unique();
            $table->string('destination_path', 512);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('website_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 100)->index();
            $table->string('key', 150)->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 50);
            $table->string('url', 2048);
            $table->string('label')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['is_active', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->string('subject_label')->nullable();
            $table->json('changes')->nullable();
            $table->uuid('request_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->index(['subject_type', 'subject_id', 'created_at']);
            $table->index(['actor_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index('created_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('website_settings');
        Schema::dropIfExists('redirects');
    }
};
