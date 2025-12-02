<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChurchRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('church_records', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('record_unique_id');
            $table->bigInteger('organization_unique_id')->nullable();
            $table->bigInteger('category_unique_id')->nullable();
            $table->integer('week_reference')->nullable();
            $table->integer('week_no')->nullable();
            $table->string('week_volume')->nullable();
            $table->string('service_date_time')->nullable();
            $table->string('service_time')->nullable();
            $table->string('service_timezone')->nullable();
            $table->float('value')->nullable();
            $table->bigInteger('service_unique_time_id')->nullable();
            $table->bigInteger('event_unique_id')->nullable();
            $table->bigInteger('campus_unique_id')->nullable();
             $table->string('record_created_at')->nullable();
             $table->string('record_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('church_records');
    }
}
