<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMontos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // tabla afip
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impNeto` DECIMAL(14,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impIVA` DECIMAL(14,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `descuento` DECIMAL(14,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impTotal` DECIMAL(14,2) NOT NULL");

        // tabla orders
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_efec` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_tarj` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_transf` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_cheque` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_dolares` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `descuento` DOUBLE(14,2) NOT NULL");

        // tabla controls
        DB::statement("ALTER TABLE `controls` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");

        // tabla deudas
        DB::statement("ALTER TABLE `deudas` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");

        // tabla orders_products
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `cantidad` DOUBLE(14,3) NOT NULL");
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `costo_x_uni` DOUBLE(15,3) NOT NULL");

        // tabla orders_services
        DB::statement("ALTER TABLE `orders_services` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");

        // tabla products
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `pedido` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `quedan` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `aviso` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `costo` DOUBLE(14,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `monto` DOUBLE(14,2) NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // tabla afip
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impNeto` DECIMAL(7,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impIVA` DECIMAL(7,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `descuento` DECIMAL(7,2) NOT NULL");
        DB::statement("ALTER TABLE `afip` MODIFY COLUMN `impTotal` DECIMAL(7,2) NOT NULL");

        // tabla orders
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `monto` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_efec` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_tarj` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_transf` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_cheque` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `pago_dolares` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `descuento` DOUBLE(8,2) NOT NULL");

        // tabla controls
        DB::statement("ALTER TABLE `controls` MODIFY COLUMN `monto` DOUBLE(8,2) NOT NULL");

        // tabla deudas
        DB::statement("ALTER TABLE `deudas` MODIFY COLUMN `monto` DOUBLE(8,2) NOT NULL");

        // tabla orders_products
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `cantidad` DOUBLE(8,3) NOT NULL");
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `monto` DOUBLE(12,2) NOT NULL");
        DB::statement("ALTER TABLE `orders_products` MODIFY COLUMN `costo_x_uni` DOUBLE(12,3) NOT NULL");

        // tabla orders_services
        DB::statement("ALTER TABLE `orders_services` MODIFY COLUMN `monto` DOUBLE(8,2) NOT NULL");

        // tabla products
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `pedido` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `quedan` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `aviso` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `costo` DOUBLE(8,2) NOT NULL");
        DB::statement("ALTER TABLE `products` MODIFY COLUMN `monto` DOUBLE(8,2) NOT NULL");
    }
}
