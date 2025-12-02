<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('service_times', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('cm_id')->nullable()->unique();
            $table->unsignedBigInteger('campus_id')->nullable();
            $table->tinyInteger('day_of_week')->nullable();
            $table->time('time_of_day')->nullable();
            $table->string('timezone')->nullable();
            $table->string('relation_to_sunday')->nullable();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('replaces')->nullable();
            $table->unsignedBigInteger('event_id')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('church_events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_times');
    }
}
