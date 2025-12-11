<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeadcountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('headcounts', function (Blueprint $table) {
            $table->id();
            $table->string('headcount_id')->unique();
            $table->integer('total')->nullable();
            $table->timestamp('headcount_created_at')->nullable();
            $table->timestamp('headcount_updated_at')->nullable();
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
        Schema::dropIfExists('headcounts');
    }
}
