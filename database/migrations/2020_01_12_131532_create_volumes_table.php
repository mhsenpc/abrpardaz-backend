<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVolumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('volumes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('remote_id');
            $table->string('name')->nullable();
            $table->float('size');
            $table->unsignedInteger('machine_id')->nullable();
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('last_billing_date',0)->nullable();
            $table->timestamp('end_date',0)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('machine_id')->references('id')->on('machines');
            $table->foreign('project_id')->references('id')->on('projects');
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
        Schema::dropIfExists('volumes');
    }
}
