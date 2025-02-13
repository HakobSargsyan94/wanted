<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRowsTable extends Migration
{
    public function up()
    {
        Schema::create('rows', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique(); // Уникальный ID
            $table->string('name'); // Имя
            $table->date('date'); // Дата
            $table->timestamps(); // Временные метки
        });
    }

    public function down()
    {
        Schema::dropIfExists('rows');
    }
}
