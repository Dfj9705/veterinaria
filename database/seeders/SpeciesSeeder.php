<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Perro', 'Gato', 'Ave', 'Conejo', 'Hamster', 'Reptil', 'Otro'] as $name) {
            Species::firstOrCreate(['name' => $name]);
        }
    }
}
