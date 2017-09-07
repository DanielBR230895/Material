<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        DB::table('usuarios')->insert([
          'Nombre' => "Gerald",
          'Apellido1'=>'Salazar',
          'Apellido2'=> 'Gómez',
          'Password' => bcrypt('12345678'	),
          'id' => 'gg',
          'identificacion'=> 'gg',
          'Rol' => 'Administrador',
          'Email' => 'gabo@gmail.com',
          'telefono' => '6969-6969',
          'Habilitado' => true,
          'created_at' => date("Y-m-d H:i:s"),
          'updated_at' => date("Y-m-d H:i:s"),
        ]);
    }
}
