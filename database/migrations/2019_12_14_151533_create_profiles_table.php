<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile')->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->string('postal_code')->nullable();
            $table->string('address')->nullable();
            $table->boolean('organization')->default(false);
            $table->string('organization_name')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('national_code')->nullable()->unique();
            $table->string('national_card_front')->nullable();
            $table->integer('national_card_front_status')->default(0);
            $table->string('national_card_front_reason')->nullable();
            $table->string('national_card_back')->nullable();
            $table->integer('national_card_back_status')->default(0);
            $table->string('national_card_back_reason')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->integer('birth_certificate_status')->default(0);
            $table->string('birth_certificate_reason')->nullable();
            $table->integer('validation_status')->default(0);
            $table->string('validation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profiles');
    }
}
