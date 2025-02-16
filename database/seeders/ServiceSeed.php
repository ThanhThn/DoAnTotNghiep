<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = ['water', 'wifi', 'electricity', 'garbage', 'parking'];
        foreach ($services as $service) {
            Service::create(['name' => $service]);
        }
    }
}
