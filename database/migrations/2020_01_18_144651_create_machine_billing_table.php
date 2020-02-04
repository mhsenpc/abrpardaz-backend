<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMachineBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('machine_billing', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('machine_id');
            $table->integer('plan_id');
            $table->timestamp('end_date')->nullable();
            $table->timestamp('last_billing_date')->nullable();
            $table->timestamps();

            $table->foreign('plan_id')->references('id')->on('plans');
            $table->foreign('machine_id')->references('id')->on('machines');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('machine_billing');
    }
}
