<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Cities table (no dependencies)
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Crafts table (no dependencies)
        Schema::create('crafts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Clients table (depends on cities)
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address');
            $table->unsignedBigInteger('city_id');
            $table->string('profile_image')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });

        // Craftsmen table (depends on cities and crafts)
        Schema::create('craftsmen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('craft_id');
            $table->string('profile_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->timestamps();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('craft_id')->references('id')->on('crafts')->onDelete('cascade');
        });

        // Phones table (depends on clients and craftsmen)
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->morphs('phoneable');
            $table->timestamps();
        });

        // Craftsman Done Jobs table (depends on craftsmen)
        Schema::create('craftsman_done_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('rating', 3, 2)->default(0);
            $table->timestamps();
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
        });

        // Craftsman Done Jobs Images table (depends on craftsman_done_jobs)
        Schema::create('craftsman_done_jobsimages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_done_job_id');
            $table->string('image_path');
            $table->timestamps();
            $table->foreign('craftsman_done_job_id')->references('id')->on('craftsman_done_jobs')->onDelete('cascade');
        });

        // Jobs Offers table (depends on clients, cities, and crafts)
        Schema::create('jobs_offers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('title');
            $table->text('description');
            $table->string('address');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('craft_id');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('craft_id')->references('id')->on('crafts')->onDelete('cascade');
        });

        // Jobs Offer Images table (depends on jobs_offers)
        Schema::create('jobs_offer_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_offer_id');
            $table->string('image_path');
            $table->timestamps();
            $table->foreign('job_offer_id')->references('id')->on('jobs_offers')->onDelete('cascade');
        });

        // Jobs Offer Replies table (depends on jobs_offers and craftsmen)
        Schema::create('jobs_offer_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_offer_id');
            $table->unsignedBigInteger('craftsman_id');
            $table->text('message');
            $table->decimal('price', 10, 2);
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            $table->foreign('job_offer_id')->references('id')->on('jobs_offers')->onDelete('cascade');
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
        });

        // Clients Ratings table (depends on clients and craftsmen)
        Schema::create('clients_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('craftsman_id');
            $table->decimal('rating', 3, 2);
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
        });

        // Craftsman Jobs table (depends on craftsmen)
        Schema::create('craftsman_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_id');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->timestamps();
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
        });

        // Craftsman Job Images table (depends on craftsman_jobs)
        Schema::create('craftsman_job_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_job_id');
            $table->string('image_path');
            $table->timestamps();
            $table->foreign('craftsman_job_id')->references('id')->on('craftsman_jobs')->onDelete('cascade');
        });

        // Active Job Finished table (depends on jobs_offers, craftsmen, and clients)
        Schema::create('active_job_finished', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_offer_id');
            $table->unsignedBigInteger('craftsman_id');
            $table->unsignedBigInteger('client_id');
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamps();
            $table->foreign('job_offer_id')->references('id')->on('jobs_offers')->onDelete('cascade');
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        // Search Images table (no dependencies)
        Schema::create('search_images', function (Blueprint $table) {
            $table->id();
            $table->string('image_path');
            $table->timestamps();
        });

        // Password Resets table (no dependencies)
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Client Notifications table (depends on clients)
        Schema::create('client_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        // Craftsman Notifications table (depends on craftsmen)
        Schema::create('craftsman_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_id');
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
        });

        // Favorite Lists table (depends on clients)
        Schema::create('favorite_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->string('name');
            $table->timestamps();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        // Favorites table (depends on craftsmen, clients, and favorite_lists)
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('craftsman_id');
            $table->unsignedBigInteger('client_id');
            $table->string('craft');
            $table->unsignedBigInteger('list_id');
            $table->timestamps();
            $table->foreign('craftsman_id')->references('id')->on('craftsmen')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('list_id')->references('id')->on('favorite_lists')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('favorites');
        Schema::dropIfExists('favorite_lists');
        Schema::dropIfExists('craftsman_notifications');
        Schema::dropIfExists('client_notifications');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('search_images');
        Schema::dropIfExists('active_job_finished');
        Schema::dropIfExists('craftsman_job_images');
        Schema::dropIfExists('craftsman_jobs');
        Schema::dropIfExists('clients_ratings');
        Schema::dropIfExists('jobs_offer_replies');
        Schema::dropIfExists('jobs_offer_images');
        Schema::dropIfExists('jobs_offers');
        Schema::dropIfExists('craftsman_done_jobsimages');
        Schema::dropIfExists('craftsman_done_jobs');
        Schema::dropIfExists('phones');
        Schema::dropIfExists('craftsmen');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('crafts');
        Schema::dropIfExists('cities');
    }
}
