<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f7f6f1">
    <meta name="description" content="A clear, calm place to begin your research analysis.">
    <title>getthesly — Start with your research</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand-link" href="{{ route('home') }}" aria-label="getthesly home">
                <x-brand-mark />
            </a>
            <p class="header-note">A calmer start to your analysis</p>
        </header>

        <main class="main-content">
            <div class="hero-intro">
                <p class="eyebrow"><span class="eyebrow-rule" aria-hidden="true"></span> For your research</p>
                <h1>Make sense of<br><em>your study.</em></h1>
                <p class="intro-copy">Begin with the question behind your work. We’ll help you find a clear way forward.</p>
            </div>

            <livewire:research-description-form />

            <p class="privacy-note">A good analysis begins with a clear question.</p>
        </main>

        <footer class="site-footer">
            <span>getthesly</span>
            <span>Thoughtful statistics for student research</span>
        </footer>
    </div>
    @livewireScripts
</body>
</html>
