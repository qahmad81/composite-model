<?php

namespace App\Services\Ai;

use App\Models\Provider;
use App\Models\ProviderModel;

interface ProviderClient
{
    public function chat(Provider $provider, ProviderModel $model, array $messages, array $options = []): AiResponse;
}
