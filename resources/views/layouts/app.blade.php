<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta name="csrf-token" content="{{ @csrf_token() }}" />

    <meta charset=utf-8>
    <meta http-equiv=X-UA-Compatible content="IE=edge">
    <meta name=viewport content="width=device-width,initial-scale=1">

    <link rel="stylesheet" href="{{ asset('/css/app.css') }}?v1.2"/>

    <title>@yield('title')</title>
</head>

<body class="mt-3 mb-3">

<div class="container">
    <div id="app">

        @yield('header')

        @yield('content')

    </div>
</div>

<script src="{{ asset('/js/app.js') }}?v1.2"></script>

</body>
</html>