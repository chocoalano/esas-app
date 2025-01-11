<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Jalankan perintah artisan shield:install
        Artisan::call('shield:install app');
        $this->call([
            // ComDeptLvlPosition::class,
            // UserSeeder::class,
            // PermitSeeder::class,
        ]);
    }
}
