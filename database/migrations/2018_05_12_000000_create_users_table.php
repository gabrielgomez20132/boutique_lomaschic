<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre', 50);
            $table->integer('dni')->unsigned()->unique()->nullable();
            $table->string('email', 50)->unique()->nullable();
            $table->string('password')->nullable();
            $table->integer('id_uType')->unsigned();
            $table->boolean('activo')->default(true);

            $table->integer('dni_cliente')->unsigned()->nullable();
            $table->string('telefono_cliente')->nullable();

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
