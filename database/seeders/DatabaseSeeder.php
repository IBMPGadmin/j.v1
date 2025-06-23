<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\GovernmentLinkSeeder;
use Database\Seeders\RCICDeadlineSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GovernmentLinkSeeder::class,
            RCICDeadlineSeeder::class,
            RCICDeadlinesTableSeeder::class,
            LegalKeyTermsTableSeeder::class,
        ]);
    }
}
