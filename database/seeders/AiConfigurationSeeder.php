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
        AiConfiguration::create([
            'llm_provider' => 'gemini',
            'base_url'     => null,
            'llm_model'    => 'gemini-1.5-pro',
            'llm_api_key'  => null,
        ]);
    }
}