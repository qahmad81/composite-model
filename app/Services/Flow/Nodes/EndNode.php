<?php

namespace App\Services\Flow\Nodes;

use App\Services\Flow\FlowContext;
use App\Services\Flow\NodeResult;

class EndNode extends BaseNode
{
    public function execute(FlowContext $context): NodeResult
    {
        $outputFormat = $this->config['output_format'] ?? 'text';
        $output = $context->accumulated_output ?: '';

        if ($outputFormat === 'json') {
            // Try to wrap it in JSON if it's not already
            if (!str_starts_with(trim($output), '{') && !str_starts_with(trim($output), '[')) {
                $output = json_encode(['response' => $output]);
            }
        } elseif ($outputFormat === 'markdown') {
            // Ensure no extra formatting if it's already markdown, 
            // but this is mostly a visual hint for the UI
        }

        return new NodeResult(
            $output,
            0, 0, 0,
            ['output_format' => $outputFormat, 'status' => 'ended']
        );
    }
}
