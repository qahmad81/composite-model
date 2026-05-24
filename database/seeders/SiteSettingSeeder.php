<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'internal_multiplier' => '1.5',
            'uc_multiplier' => '1.2',
            'uc_gateway_url' => 'http://localhost:8880/api/v1',
            'log_executions' => 'true',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
