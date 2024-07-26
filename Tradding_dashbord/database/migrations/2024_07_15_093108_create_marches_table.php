<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMarchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marches', function (Blueprint $table) {
            $table->id(); // Crée la colonne `id` comme clé primaire
            $table->string('titre'); // Crée la colonne `titre` de type chaîne de caractères
            $table->string('icon');  // Crée la colonne `icon` de type chaîne de caractères
            $table->timestamps();    // Crée les colonnes `created_at` et `updated_at`
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marches');
    }
}
