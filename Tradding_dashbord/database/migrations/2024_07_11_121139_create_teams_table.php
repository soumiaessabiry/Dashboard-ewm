<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('league_id');
            $table->foreign('league_id')->references('id')->on('leagues')->onDelete('cascade');
            $table->string('team_code')->unique();
            $table->string('team_name');
            $table->integer('number_of_players')->default(0); // Définit une valeur par défaut
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
