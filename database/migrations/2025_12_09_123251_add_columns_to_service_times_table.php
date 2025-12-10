<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToServiceTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_times', function (Blueprint $table) {
            $table->string('campus_name')->nullable();
            $table->string('event_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_times', function (Blueprint $table) {
            $table->dropColumn(['campus_name','event_name']);
        });
    }
}
