<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeagueCsvDataTable extends Migration
{
    public function up()
    {
       

        Schema::create('league_csv_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->string('HEURES');
            for ($i = 1; $i <= 25; $i++) {
                $table->string('JOUR_' . $i);
            }
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('league_csv_data');
    }
}
