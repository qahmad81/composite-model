<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title }} - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-2xl font-bold text-indigo-600">Composite Model</a>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    <a href="/" class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">Home</a>
                    @foreach(\App\Models\SitePage::where('show_in_menu', true)->where('is_published', true)->orderBy('sort_order')->get() as $menuPage)
                        <a href="/pages/{{ $menuPage->slug }}" class="{{ request()->is('pages/'.$menuPage->slug) ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            {{ $menuPage->title }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <article class="prose prose-indigo lg:prose-lg mx-auto">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 mb-8">{{ $page->title }}</h1>
            <div class="mt-6 text-gray-700 leading-relaxed">
                {!! $page->html_content !!}
            </div>
        </article>
    </main>

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <p class="text-center text-base text-gray-400">
                &copy; {{ date('Y') }} {{ \App\Models\SiteSetting::getSetting('footer_copyright', 'Composite Model') }}
            </p>
        </div>
    </footer>
</body>
</html>
