<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        $balance_categories = array(
            ["name"=>"Otro"],
            ["name"=>"Transaccion"],
            ["name"=>"Mercado"],
            ["name"=>"Inversion"],
            ["name"=>"Alquiler"],
            ["name"=>"Expensas"],
        );

        $economic_profiles = array(
            ["name" => "Conservador"],
            ["name" => "Moderado"],
            ["name" => "Agresivo"],
        );

        foreach ($balance_categories as $balance) {
            DB::table('categories')->insert($balance);
        }

        foreach ($economic_profiles as $ep) {
            DB::table('economic_profiles')->insert($ep);
        }
    }
}
