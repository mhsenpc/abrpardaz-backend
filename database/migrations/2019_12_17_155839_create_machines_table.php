<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('remote_id')->nullable();
            $table->string('public_ipv4')->nullable();
            $table->string('password')->nullable();
            $table->integer('user_id');
            $table->integer('plan_id');
            $table->integer('image_id');
            $table->integer('ssh_key_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('image_id')->references('id')->on('images');
            $table->foreign('ssh_key_id')->references('id')->on('ssh_keys');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machines');
    }
}
