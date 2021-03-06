<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tusers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('nama');
            $table->text('email');
            $table->text('password');
            $table->text('org');
            $table->unsignedBigInteger('idLevel');
            $table->timestamps();

            $table->foreign('idLevel')->references('id')->on('tlevels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tusers');
    }
}
