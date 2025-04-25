<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
            <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Figtree', sans-serif;
            color: #1a202c;
            background-color: #f7fafc;
        }
        .container {
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .logo {
            margin-bottom: 2rem;
            text-align: center;
        }
        .logo h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #4f46e5;
            margin-bottom: 0.5rem;
        }
        .logo p {
            font-size: 1.2rem;
            color: #6b7280;
        }
        .card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .card h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            text-align: center;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
            margin-top: 1rem;
        }
        .btn-primary {
            color: white;
            background-color: #4f46e5;
            border: 1px solid #4f46e5;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
            </style>
    </head>
<body>
    <div class="container">
        <div class="logo">
            <h1>{{ config('app.name') }}</h1>
            <p>Laravel 12 Project with Magic Link Authentication</p>
        </div>

        <div class="card">
            <h2>Welcome to {{ config('app.name') }}</h2>
            <p>Please log in to access the application features.</p>
            <a href="{{ route('login.magic') }}" class="btn btn-primary">Log in with Magic Link</a>
        </div>
    </div>
    </body>
</html>
