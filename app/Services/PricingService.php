<?php

namespace App\Services;

use App\Models\SiteSetting;

class PricingService
{
    public function calculateCost(int $baseCost, string $tokenType): int
    {
        $multiplier = 1.0;

        if ($tokenType === 'internal') {
            $multiplier = (float) SiteSetting::where('key', 'internal_multiplier')->value('value') ?: 1.5;
        } elseif ($tokenType === 'uc') {
            $multiplier = (float) SiteSetting::where('key', 'uc_multiplier')->value('value') ?: 1.2;
        }

        return (int) round($baseCost * $multiplier);
    }
}
