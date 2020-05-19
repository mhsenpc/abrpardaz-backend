<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->boolean('suspend')->default(false);
            $table->string('password');
            $table->integer('referrer_id')->nullable();
            $table->integer('user_limit_id')->nullable();
            $table->integer('profile_id');
            $table->string('provider_user_id')->nullable();
            $table->string('remote_user_id')->nullable();
            $table->string('remote_user_name')->nullable();
            $table->string('remote_password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_billing_date');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('profile_id')->references('id')->on('profiles');
            $table->foreign('user_limit_id')->references('id')->on('user_limits');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
