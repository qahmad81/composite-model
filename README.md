# Composite Model - AI Pipeline Builder

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Composite Model is a powerful, visual AI pipeline builder that allows you to create, deploy, and manage complex multi-model workflows. It provides a seamless transition from visual design to a production-ready OpenAI-compatible API.

## 🚀 Features

- **Visual Flow Builder**: Intuitive drag-and-drop interface powered by React Flow for designing AI pipelines.
- **OpenAI Compatible API**: Use your custom pipelines with any existing OpenAI-compatible application.
- **Multi-Provider Support**: Built-in support for OpenAI, Anthropic, Gemini, and more.
- **Advanced Logic Nodes**:
  - **Parallel**: Execute multiple branches simultaneously.
  - **Aggregator**: Combine results from multiple branches.
  - **Loop**: Iterate over data sets.
  - **Condition & Router**: Logical branching in your workflows.
  - **Data Processor**: Custom filtering and transformation.
- **Internal Credits System**: Integrated billing and usage tracking for users and models.
- **Real-time Streaming**: Full support for server-sent events (SSE) in model executions.
- **Execution Analytics**: Detailed logs, cost tracking, and performance monitoring.
- **Filament Admin Panel**: Comprehensive management interface for models, providers, users, and flows.

## 🛠 Tech Stack

- **Backend**: Laravel 11 (PHP 8.3)
- **Admin Panel**: Filament v3
- **GUI Builder**: React + React Flow (Vite)
- **Database**: MySQL / MariaDB
- **UI Styling**: Tailwind CSS

## 🏁 Quick Start

### Prerequisites

- PHP 8.3+
- Composer
- Node.js & npm
- MySQL / MariaDB

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/qahmad81/composite-model.git
   cd composite-model
   ```

2. Install dependencies:
   ```bash
   composer install
   npm install
   ```

3. Setup environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure your database in `.env`.

5. Run migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

6. Build assets:
   ```bash
   npm run build
   ```

7. Start the development server:
   ```bash
   php artisan serve
   ```

## 🔌 API Usage

Integration is as simple as changing the `baseUrl` in your OpenAI client.

```bash
curl https://your-domain.com/v1/chat/completions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{
    "model": "your-pipeline-slug",
    "messages": [{"role": "user", "content": "Hello!"}],
    "stream": true
  }'
```

## 🏛 Architecture Overview

Composite Model follows a modular architecture where every pipeline is represented as a directed acyclic graph (DAG). The `FlowEngine` traverses these nodes, handling model requests, conditional branching, and data aggregation in real-time.

## 🤖 Developed by Verdent

This repository was developed using the **Verdent AI agent**, reaching 90% completion in under 4 hours across 8 phases of continuous autonomous development.

Developed by **Ahmad Odeh** via Verdent.
- **Website**: [odehit.com](https://odehit.com)
- **GitHub**: [@qahmad81](https://github.com/qahmad81)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
