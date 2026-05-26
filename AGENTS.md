# Composite Model - Project Overview for AI Agents

This document provides essential context for AI agents working on the Composite Model project.

## Project Overview
Composite Model is a high-performance AI orchestration platform built with Laravel 11. It allows users to build "Composite Modules" — complex AI workflows visually designed using a React Flow-based GUI. These workflows can orchestrate multiple AI models from different providers (OpenAI, Anthropic, Gemini, etc.) in parallel, sequences, or loops, with conditional routing and data processing.

The project works within an ecosystem alongside:
- **Universal Credits**: Handles external payments and credit management.
- **ThreadCore**: A conversation management system and source for provider/model data models.

## Tech Stack
- **Backend**: Laravel 11, PHP 8.3.12 (C:\MAMP\bin\php\php8.3.12\php.exe)
- **Database**: MySQL (InnoDB, BIGINT for currency fields)
- **Admin Panel**: Filament v3
- **Frontend (Editor)**: React 19 + Vite + React Flow (@xyflow/react)
- **Styling**: Tailwind CSS (CDN for public pages, PostCSS for components)

## Environment Setup
- **PHP Path**: `C:\MAMP\bin\php\php8.3.12\php.exe`
- **Database**: `composite_model`
- **Credentials**: `root` / `root`
- **Environment Variables**:
  ```powershell
  $env:DB_DATABASE='composite_model'
  $env:DB_USERNAME='root'
  $env:DB_PASSWORD='root'
  ```
- **Execution Commands**:
  - Dev Server: `php artisan serve`
  - Vite: `npm run dev`
  - Build: `npm run build`
  - Tests: `$env:DB_DATABASE='composite_model'; $env:DB_USERNAME='root'; $env:DB_PASSWORD='root'; & "C:\MAMP\bin\php\php8.3.12\php.exe" vendor/bin/phpunit`

## Architecture
- **API Layer**: Provides an OpenAI-compatible interface (`/v1/chat/completions`). Supports streaming by default.
- **Flow Engine**: Key logic in `App\Services\Flow\FlowEngine`. Executes `flow_json` structures by traversing nodes and edges.
- **Token System**: Uses `cm_` prefix for internal tokens. Tracks `PendingReservation` before execution and confirms/cancels after. Supports future UC (`uc_` prefix) integration.
- **Provider System**: Multi-driver system (handled by `ProviderClientManager`) supporting various AI backends.
- **Flow Builder**: A visual GUI built with React Flow for designing complex AI logic.

## Key Concepts
- **Composite Module**: A database record (`CompositeModule` model) containing a `flow_json` blob.
- **Reservation Pattern**: 
  1. `reserve()`: Lock credits before execution based on `top_credit_limit`.
  2. `execute()`: Run the flow.
  3. `confirm()` or `cancel()`: Finalize transaction based on actual usage.
- **Node Types**:
  - `start`: Flow entry point.
  - `end`: Flow exit point, formats output.
  - `model`: AI model invocation.
  - `parallel`: Split flow into multiple concurrent paths.
  - `aggregator`: Collects results from parallel paths.
  - `loop`: Repeat a section of the flow.
  - `condition`: If/Else branching based on PHP expressions.
  - `router`: Switch/Case style routing.
  - `data_processor`: Custom PHP script execution for data manipulation.

## API Endpoints
- `POST /api/v1/chat/completions`: OpenAI compatible endpoint. Authenticates via `Bearer` token (internal tokens start with `cm_`).
- `GET /api/v1/models`: Returns available composite modules as models.
- `GET/PUT /api/flow-editor/{module}`: Management routes for the visual builder.

## Development Rules
1. **Always use PHP 8.3** full path: `C:\MAMP\bin\php\php8.3.12\php.exe`.
2. **Set environment variables** (DB credentials) before running any artisan or phpunit commands.
3. **Run tests** after every change to ensure 100% pass rate (22 tests currently).
4. **Adhere to the Reservation Pattern** for all billing-related changes.
5. **Commit and push** after each major phase.
