<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('events_data', function (Blueprint $table) {
            $table->id();

            $table->string('event_time_id');   
            $table->string('event_id');   
            $table->string('event_name');          
            $table->string('service_name')->nullable(); 
            $table->string('week_reference')->nullable();
            $table->date('service_date');         
            $table->time('service_time');       
            $table->string('headcount_id')->nullable();     
            $table->string('attendance_id')->nullable();
            $table->integer('value')->default(0);    
            $table->enum('headcount_type', ['regular', 'guest', 'volunteer', 'manual'])->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('location_id')->nullable();
            $table->timestamp('headcount_created_at')->nullable();
            $table->timestamp('headcount_updated_at')->nullable();
            $table->timestamp('synced_at')->useCurrent();
            $table->timestamps();
            $table->index(['event_time_id', 'user_id']);
            $table->index(['service_date', 'service_time']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events_data');
    }
}
