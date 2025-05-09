<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Content Processor</title>

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
        .form-group {
            margin-bottom: 1rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.25rem;
            font-size: 1rem;
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
        .btn-primary {
            color: white;
            background-color: #4f46e5;
            border: 1px solid #4f46e5;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .btn-secondary {
            color: #1a202c;
            background-color: #e2e8f0;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background-color: #cbd5e1;
            border-color: #cbd5e1;
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
        .alert-error {
            color: #b91c1c;
            background-color: #fee2e2;
        }
        .examples {
            margin-top: 2rem;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }
        .tab-links {
            display: flex;
            margin-bottom: 1rem;
        }
        .tab-link {
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border-bottom: 2px solid transparent;
            cursor: pointer;
        }
        .tab-link.active {
            border-bottom-color: #4f46e5;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">{{ config('app.name') }}</div>
            <div class="user-info">
                <span class="user-name">{{ Auth::user()->name }}</span>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Logout</button>
                </form>
            </div>
        </header>

        <div class="tab-links">
            <a href="{{ route('content.processor') }}" class="tab-link active">Process Content</a>
            <a href="{{ route('content.processor.list') }}" class="tab-link">View All Content</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-title">Content Processor</div>
            <p>Submit a URL to extract and process its content. The system supports YouTube videos and articles.</p>
            
            <form method="POST" action="{{ route('content.processor.process') }}">
                @csrf
                <div class="form-group">
                    <label for="url" class="form-label">Content URL</label>
                    <input type="url" id="url" name="url" class="form-input" placeholder="https://example.com" value="{{ old('url') }}" required>
                    @error('url')
                        <div class="text-error" style="color: #ef4444; margin-top: 0.25rem;">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Process Content</button>
            </form>

            <div class="examples">
                <h3>Example URLs you can try:</h3>
                <ul>
                    <li>YouTube: <code>https://www.youtube.com/watch?v=dQw4w9WgXcQ</code></li>
                    <li>Article: <code>https://example.com/article</code></li>
                </ul>
                <p><strong>Note:</strong> Processing YouTube videos may take several minutes, especially for longer videos.</p>
            </div>
        </div>
    </div>
</body>
</html> 