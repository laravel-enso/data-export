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

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('data_exports');
    }
}
