<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServerActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('server_activities', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('machine_id');
            $table->unsignedInteger('user_id');
            $table->string('message');
            $table->timestamps();

            $table->foreign('machine_id')->references('id')->on('machines');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('server_activities');
    }
}
