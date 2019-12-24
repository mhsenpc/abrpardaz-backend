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
            $table->string('mobile')->nullable()->unique();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('national_code')->nullable()->unique();
            $table->string('national_card_front')->nullable();
            $table->timestamp('national_card_front_verified_at')->nullable();
            $table->string('national_card_back')->nullable();
            $table->timestamp('national_card_back_verified_at')->nullable();
            $table->string('birth_certificate')->nullable();
            $table->timestamp('birth_certificate_verified_at')->nullable();
            $table->boolean('is_active')->default(false);
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
