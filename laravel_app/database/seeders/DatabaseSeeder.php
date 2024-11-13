<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//        $this->call(ProcessDemoCrm::class);
//        $this->call(CreateDefaultPermission::class);
//        $this->call(CreateDefaultWebSiteAndZone::class);
        $this->call(CreateDefaultPostIndustry::class);
    }
}
