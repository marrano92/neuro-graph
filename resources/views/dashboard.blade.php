<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Dashboard</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f7fafc;
            color: #1a202c;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 600;
            color: #4f46e5;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .user-name {
            font-weight: 500;
        }
        .card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            font-weight: 500;
            text-align: center;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-danger {
            color: white;
            background-color: #ef4444;
            border: 1px solid #ef4444;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-success {
            color: #0f766e;
            background-color: #ccfbf1;
        }
        .token-box {
            background-color: #f1f5f9;
            padding: 1rem;
            border-radius: 0.25rem;
            overflow-x: auto;
            margin-top: 1rem;
            font-family: monospace;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">{{ config('app.name') }}</div>
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </header>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="card">
            <div class="card-title">Welcome to your Dashboard</div>
            <p>You have successfully logged in using a magic link.</p>
        </div>

        @if(session('api_token'))
            <div class="card">
                <div class="card-title">Your API Token</div>
                <p>Here is your API token for making authenticated requests:</p>
                <div class="token-box">
                    {{ session('api_token') }}
                </div>
                <p class="text-muted" style="margin-top: 0.5rem;">This token is only shown once. Make sure to save it!</p>
            </div>
        @endif

        <div class="grid">
            <div class="card">
                <div class="card-title">Profile</div>
                <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                <p><strong>Joined:</strong> {{ Auth::user()->created_at->format('F j, Y') }}</p>
            </div>

            <div class="card">
                <div class="card-title">Quick Links</div>
                <ul>
                    <li><a href="{{ route('search.page') }}">Search</a></li>
                    <li><a href="{{ route('horizon.demo') }}">Horizon Demo</a></li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html> 