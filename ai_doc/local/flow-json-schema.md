# Flow JSON Schema Documentation

Every `CompositeModule` in the system stores its execution logic in a `flow_json` column. This document describes the structure and node types supported by the `FlowEngine`.

## Root Structure
```json
{
  "nodes": [],
  "edges": [],
  "startNodeId": "optional_id"
}
```

## Node Types and Configurations

### `start`
The entry point of the flow.
```json
{
  "id": "node_1",
  "type": "start",
  "position": { "x": 0, "y": 0 },
  "data": {}
}
```

### `end`
The exit point of the flow.
```json
{
  "id": "node_99",
  "type": "end",
  "position": { "x": 500, "y": 500 },
  "data": {
    "output_format": "text" // "text", "json", "markdown"
  }
}
```

### `model`
Invokes an AI model.
```json
{
  "id": "node_2",
  "type": "model",
  "data": {
    "provider_model_id": 1, // Reference to provider_models table
    "model_name": "OpenAI: GPT-4o",
    "system_prompt": "You are a helpful assistant.",
    "fallback_model_id": null
  }
}
```

### `parallel`
Splits execution into multiple parallel paths.
```json
{
  "id": "node_3",
  "type": "parallel",
  "data": {}
}
```

### `aggregator`
Collects results from parallel executions.
```json
{
  "id": "node_4",
  "type": "aggregator",
  "data": {
    "timeout_ms": 30000,
    "aggregation_strategy": "concat" // "concat", "json_merge", "most_frequent"
  }
}
```

### `condition`
Branching logic.
```json
{
  "id": "node_5",
  "type": "condition",
  "data": {
    "expression": "$context->last_output == 'yes'" // PHP expression
  }
}
```

### `data_processor`
Custom script execution.
```json
{
  "id": "node_6",
  "type": "dataProcessor",
  "data": {
    "script": "return strtolower($input);" // PHP script snippet
  }
}
```

## Edge Format
Edges define the flow of execution between nodes.
```json
{
  "id": "e1-2",
  "source": "node_1",
  "target": "node_2"
}
```

## Complete Example (Start -> Model -> End)
```json
{
  "nodes": [
    { "id": "1", "type": "start", "position": { "x": 250, "y": 0 }, "data": {} },
    { 
      "id": "2", 
      "type": "model", 
      "position": { "x": 250, "y": 100 }, 
      "data": { "provider_model_id": 4, "model_name": "DeepSeek: Chat", "system_prompt": "Summarize briefly." } 
    },
    { "id": "3", "type": "end", "position": { "x": 250, "y": 200 }, "data": { "output_format": "text" } }
  ],
  "edges": [
    { "id": "e1-2", "source": "1", "target": "2" },
    { "id": "e2-3", "source": "2", "target": "3" }
  ]
}
```
