<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBackupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('remote_id')->nullable();
            $table->float('size')->nullable();
            $table->integer('machine_id');
            $table->integer('user_id');
            $table->integer('image_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('machine_id')->references('id')->on('machines');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('image_id')->references('id')->on('images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
