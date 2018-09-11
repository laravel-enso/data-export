<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataExportsTable extends Migration
{
    public function up()
    {
        Schema::create('data_exports', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('created_by')->unsigned();
            $table->foreign('created_by')->references('id')
                ->on('users');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_exports');
    }
}
