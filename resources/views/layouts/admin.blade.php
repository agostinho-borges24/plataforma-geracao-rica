<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Admin</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="admin-body">
    <header class="admin-topbar">
        <span class="admin-topbar__brand">{{ config('app.name') }} — Admin</span>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="admin-topbar__logout">Sair</button>
        </form>
    </header>

    <main class="admin-main">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>