<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('back_id')->nullable();
            $table->unsignedBigInteger('front_id')->nullable();
            $table->unsignedBigInteger('tab_id');
            $table->foreign('tab_id')->references('id')->on('tabs');
            $table->string('request_method',8);
            $table->string('url');
            $table->string('parameters')->nullable();
            $table->smallInteger('status_code');
            $table->text('response_header');
            $table->longText('response_body');
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
        Schema::dropIfExists('requests');
    }
}
