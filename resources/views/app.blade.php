<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'DEMO') }}</title>
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>
<body class="bg-stone-50 text-stone-900 antialiased">
    @inertia
</body>
</html>
