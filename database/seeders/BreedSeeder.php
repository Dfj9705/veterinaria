<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Species;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BreedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $breeds = [
            'Perro' => [
                'Mestizo',
                'Labrador',
                'Golden Retriever',
                'Pastor Alemán',
                'Chihuahua',
                'Poodle',
                'Bulldog',
                'Husky Siberiano',
                'Rottweiler',
                'Beagle',
            ],
            'Gato' => [
                'Mestizo',
                'Siamés',
                'Persa',
                'Maine Coon',
                'Bengalí',
                'Angora',
                'Esfinge',
            ],
            'Ave' => [
                'Canario',
                'Periquito',
                'Loro',
                'Cacatúa',
                'Agapornis',
            ],
            'Conejo' => [
                'Enano',
                'Cabeza de León',
                'Belier',
                'Rex',
            ],
            'Hamster' => [
                'Sirio',
                'Ruso',
                'Roborovski',
            ],
            'Reptil' => [
                'Iguana',
                'Tortuga',
                'Gecko',
                'Serpiente',
            ],
            'Otro' => [
                'No especificada',
            ],
        ];

        foreach ($breeds as $speciesName => $items) {
            $species = Species::where('name', $speciesName)->first();

            if (!$species) {
                continue;
            }

            foreach ($items as $breedName) {
                Breed::firstOrCreate([
                    'species_id' => $species->id,
                    'name' => $breedName,
                ]);
            }
        }
    }
}
