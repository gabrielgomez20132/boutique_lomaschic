<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActivaToProductCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->boolean('activa')->after('nombre')->default(true);
        });
        Schema::table('product_colors', function (Blueprint $table) {
            $table->boolean('activa')->after('nombre')->default(true);
        });
        Schema::table('product_talles', function (Blueprint $table) {
            $table->boolean('activa')->after('nombre')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('activa');
        });
        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropColumn('activa');
        });
        Schema::table('product_talles', function (Blueprint $table) {
            $table->dropColumn('activa');
        });
    }
}
