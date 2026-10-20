<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased {{ request()->has('dark') || request()->get('theme') === 'dark' ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title inertia>{{ config('app.name', 'Gelawitto ERP') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@700&family=Fredoka:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('theme') === 'dark' || urlParams.has('dark')) {
            localStorage.setItem('gelawitto-theme', 'dark');
            document.documentElement.classList.add('dark');
        } else if (urlParams.get('theme') === 'light' || urlParams.has('light')) {
            localStorage.setItem('gelawitto-theme', 'light');
            document.documentElement.classList.remove('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="min-h-full bg-background text-foreground font-sans selection:bg-rubor selection:text-frambuesa">
    @inertia
</body>
</html>
