<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompositeModule;
use Illuminate\Http\JsonResponse;

class ModelsController extends Controller
{
    public function index(): JsonResponse
    {
        $modules = CompositeModule::where('is_enabled', true)->get();

        $data = $modules->map(function ($module) {
            return [
                'id' => $module->slug,
                'object' => 'model',
                'created' => $module->created_at->timestamp,
                'owned_by' => 'composite-model',
            ];
        });

        return response()->json([
            'object' => 'list',
            'data' => $data,
        ]);
    }
}
