<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('users')->truncate();
        
        DB::table('users')->insert([
            'nombre' => 'Administrador',
            'dni' => '123456',
            'email' => 'lomaschic@gmail.com',
            'password' => bcrypt('2026'),
            'id_uType' => '1',
            'role_id' => '1',
        ]);

        DB::table('users')->insert([
            'nombre' => 'General',
            'id_uType' => '2 ',
        ]);

        
        DB::table('users')->insert([
            'nombre' => 'UsuarioVentas',
            'dni' => '12345',
            'email' => 'mail2@gmail.com',
            'password' => bcrypt('12345'),
            'id_uType' => '1',
            'role_id' => '2',
        ]);

        /* DB::table('users')->insert([
            'nombre' => 'Usuario02',
            'dni' => '98765',
            'email' => 'mail3@gmail.com',
            'password' => bcrypt('98765'),
            'id_uType' => '1',
            'role_id' => '2',
        ]); */


        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
