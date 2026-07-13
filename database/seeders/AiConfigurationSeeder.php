<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AiConfiguration;

class AiConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiConfiguration::firstOrCreate([], app(\App\Services\AiConfigurationService::class)->defaults());
    }
}
