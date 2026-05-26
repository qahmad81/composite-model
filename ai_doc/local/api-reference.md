# Composite Model API Reference

The Composite Model platform provides an OpenAI-compatible API for executing complex AI workflows.

## Authentication
All requests must include a `Authorization` header with a Bearer token.
- Internal tokens start with `cm_`.
- Tokens are managed in the Filament Admin panel.

```bash
Authorization: Bearer cm_xxxx...
```

## Endpoints

### 1. Chat Completions
Executes a composite module flow.

**URL**: `POST /api/v1/chat/completions`

**Request Body (JSON)**:
| Field | Type | Description |
|---|---|---|
| `model` | string | The slug of the Composite Module to execute. |
| `messages` | array | Array of message objects (role/content). |
| `stream` | boolean | Whether to stream the response (default: `true`). |

**Example Curl**:
```bash
curl http://localhost:8000/api/v1/chat/completions \
  -H "Authorization: Bearer cm_YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "model": "consultant-pro",
    "messages": [{"role": "user", "content": "How to optimize a Laravel app?"}],
    "stream": false
  }'
```

**Response (Non-streaming)**: Matches OpenAI Chat Completion format.
**Response (Streaming)**: Matches OpenAI Server-Sent Events (SSE) format.

---

### 2. List Models
Lists available composite modules that can be called as "models".

**URL**: `GET /api/v1/models`

**Example Curl**:
```bash
curl http://localhost:8000/api/v1/models \
  -H "Authorization: Bearer cm_YOUR_TOKEN"
```

---

### 3. Flow Editor API
Used by the built-in visual builder.

- `GET /api/flow-editor/{module_id}`: Get flow JSON.
- `PUT /api/flow-editor/{module_id}`: Update flow JSON.
- `GET /api/flow-editor/{module_id}/models`: List provider models for selection in nodes.

## Errors
The API returns OpenAI-style error objects.
- `401`: Unauthorized (Invalid token)
- `402`: Insufficient Balance
- `404`: Model Not Found
- `500`: Internal Engine Error / Flow Execution Error
