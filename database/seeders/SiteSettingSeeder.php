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
            'site_name' => 'Composite Model',
            'site_description' => 'Build and deploy AI pipelines with visual flow editor',
            'github_url' => 'https://github.com/qahmad81/composite-model',
            'footer_copyright' => '2026 OdehIT.com. All rights reserved.',
            'website_url' => 'https://odehit.com',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
