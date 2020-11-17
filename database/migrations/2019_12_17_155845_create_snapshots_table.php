<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSnapshotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('remote_id')->nullable();
            $table->float('size')->nullable();
            $table->unsignedInteger('machine_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('image_id');
            $table->text('description')->nullable();
            $table->timestamp('last_billing_date',0)->nullable();
            $table->timestamp('end_date',0)->nullable();
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
        Schema::dropIfExists('snapshots');
    }
}
