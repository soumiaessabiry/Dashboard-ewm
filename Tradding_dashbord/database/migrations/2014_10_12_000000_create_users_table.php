<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique(); // Ajout de username
            $table->string('email')->unique();
            $table->string('password');
            // $table->string('country')->default('france');  // Ajout de country
            // $table->bigInteger('team_id')->default(null); // Ajout de team_id et le rendre nullable
            $table->string('country')->nullable(); // Ajout de country et le rendre nullable
            $table->bigInteger('team_id')->nullable(); // Ajout de team_id et le rendre nullable
            $table->string('role');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
