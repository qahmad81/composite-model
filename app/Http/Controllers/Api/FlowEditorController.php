<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompositeModule;
use App\Models\ProviderModel;
use Illuminate\Http\Request;

class FlowEditorController extends Controller
{
    public function show(CompositeModule $compositeModule)
    {
        return response()->json([
            'flow_json' => $compositeModule->flow_json ?: ['nodes' => [], 'edges' => []],
        ]);
    }

    public function update(Request $request, CompositeModule $compositeModule)
    {
        $validated = $request->validate([
            'flow_json' => 'required|array',
        ]);

        $flowJson = $validated['flow_json'];
        
        // Extract configs for Laravel services
        if (isset($flowJson['nodes'])) {
            foreach ($flowJson['nodes'] as &$node) {
                $node['config'] = $node['data'] ?? [];
            }
        }

        $compositeModule->update([
            'flow_json' => $flowJson,
        ]);

        return response()->json(['message' => 'Flow updated successfully']);
    }

    public function models()
    {
        $models = ProviderModel::with('provider')
            ->where('is_enabled', true)
            ->get()
            ->map(function ($model) {
                return [
                    'id' => $model->id,
                    'name' => $model->name,
                    'model_key' => $model->model_key,
                    'provider_name' => $model->provider->name,
                ];
            });

        return response()->json($models);
    }
}
