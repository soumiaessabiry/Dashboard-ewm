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

        Schema::create('data_marche', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marche_id');
            $table->string('HEURES');
            for ($i = 1; $i <= 25; $i++) {
                $table->string('JOUR_' . $i);
            }
            $table->string('mois_selected')->nullable(); // Inclure le champ mois_selected dans la migration

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_marche');
    }
};
