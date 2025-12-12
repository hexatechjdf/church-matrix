<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToChurchRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('church_records', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable();
            $table->string('event_name')->nullable();
            $table->string('campus_name')->nullable();
            $table->string('category_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('church_records', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'event_name','campus_name','category_name']);
        });
    }
}
