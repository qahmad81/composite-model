<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['site_name'] ?? 'Composite Model' }} - AI Pipeline Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <!-- Navbar -->
    <nav class="bg-white/80 backdrop-blur-md sticky top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">
                        {{ $settings['site_name'] ?? 'Composite Model' }}
                    </span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-600 hover:text-indigo-600 font-medium transition">Home</a>
                    @foreach($menuPages as $page)
                        <a href="/pages/{{ $page->slug }}" class="text-gray-600 hover:text-indigo-600 font-medium transition">{{ $page->title }}</a>
                    @endforeach
                    <a href="/admin" class="px-4 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <section class="py-20 lg:py-32 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center">
                <h1 class="text-5xl lg:text-7xl font-extrabold tracking-tight text-gray-900 mb-6">
                    Build and Deploy <br/>
                    <span class="text-indigo-600">AI Pipelines</span> Visually
                </h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto mb-10">
                    {{ $settings['site_description'] ?? 'Create complex multi-model AI workflows with our intuitive drag-and-drop editor. Parallel processing, conditional logic, and OpenAI-compatible API.' }}
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="/admin" class="px-8 py-4 rounded-xl bg-indigo-600 text-white text-lg font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition text-center">
                        Get Started for Free
                    </a>
                    <a href="{{ $settings['github_url'] ?? '#' }}" class="px-8 py-4 rounded-xl bg-white text-gray-900 border border-gray-200 text-lg font-bold hover:bg-gray-50 transition flex items-center justify-center gap-2">
                        <i class="fab fa-github"></i> View on GitHub
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold mb-4">Powerful Features</h2>
                <div class="w-20 h-1.5 gradient-bg mx-auto rounded-full"></div>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Visual Flow Builder</h3>
                    <p class="text-gray-600">Drag and drop nodes to create multi-step AI pipelines. Support for models, conditions, routers, and processing logic.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">OpenAI Compatible API</h3>
                    <p class="text-gray-600">Swap any OpenAI-powered app to your custom pipeline with a simple base URL change. Full support for streaming.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Multi-Provider Support</h3>
                    <h3 class="text-xl font-bold mb-3">Multi-Provider Support</h3>
                    <p class="text-gray-600">Internal support for OpenAI, Anthropic, Gemini, and more. Use multiple models in a single unified flow.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Credits & Billing</h3>
                    <p class="text-gray-600">Integrated credit system with automated deduction based on model usage and custom pricing.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Execution Analytics</h3>
                    <p class="text-gray-600">Detailed logs and analytics for every flow execution, including token usage, costs, and performance metrics.</p>
                </div>
                <div class="p-8 rounded-2xl bg-gray-50 border border-gray-100 hover:shadow-xl transition">
                    <div class="w-12 h-12 rounded-lg gradient-bg flex items-center justify-center text-white mb-6">
                        <i class="fas fa-code-merge"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Advanced Logic</h3>
                    <p class="text-gray-600">Parallel execution, loops, model fallbacks, and custom data processing filters out of the box.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="py-20 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2">
                    <h2 class="text-4xl font-bold mb-8">From Design to API <br/> in Minutes</h2>
                    <ul class="space-y-6">
                        <li class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-bold">1</div>
                            <div>
                                <h4 class="text-lg font-bold">Design your Flow</h4>
                                <p class="text-gray-600">Use our React Flow based editor to map out your AI logic.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-bold">2</div>
                            <div>
                                <h4 class="text-lg font-bold">Configure Providers</h4>
                                <p class="text-gray-600">Add your API keys and set pricing for your custom pipeline.</p>
                            </div>
                        </li>
                        <li class="flex gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full gradient-bg flex items-center justify-center text-white font-bold">3</div>
                            <div>
                                <h4 class="text-lg font-bold">Deploy and Integrate</h4>
                                <p class="text-gray-600">Call your unique API endpoint from any application.</p>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="lg:w-1/2 bg-indigo-900 rounded-3xl p-4 shadow-2xl relative">
                    <div class="aspect-video bg-indigo-950 rounded-2xl overflow-hidden flex items-center justify-center text-indigo-300">
                        <i class="fas fa-play-circle text-6xl opacity-50"></i>
                    </div>
                    <div class="absolute -bottom-8 -left-8 bg-white p-6 rounded-2xl shadow-xl border border-gray-100 hidden md:block">
                        <p class="text-sm font-bold text-gray-500 mb-1 uppercase">Total Requests</p>
                        <p class="text-3xl font-extrabold text-gray-900">1.2M+</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 border-b border-gray-800 pb-12 mb-12">
                <div>
                    <h3 class="text-2xl font-bold mb-4">{{ $settings['site_name'] ?? 'Composite Model' }}</h3>
                    <p class="text-gray-400 max-w-sm">{{ $settings['site_description'] ?? 'Building the future of autonomous AI workflows.' }}</p>
                </div>
                <div class="flex gap-6 text-2xl">
                    <a href="{{ $settings['github_url'] ?? '#' }}" class="hover:text-indigo-400 transition"><i class="fab fa-github"></i></a>
                    <a href="{{ $settings['website_url'] ?? '#' }}" class="hover:text-indigo-400 transition"><i class="fas fa-globe"></i></a>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-gray-500 text-sm">
                <p>&copy; {{ date('Y') }} {{ $settings['footer_copyright'] ?? 'OdehIT.com' }}. All rights reserved.</p>
                <p>Developed via <span class="text-indigo-400">Verdent AI</span></p>
            </div>
        </div>
    </footer>
</body>
</html>
