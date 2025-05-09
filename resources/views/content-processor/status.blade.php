<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Content Status</title>

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
        .progress-container {
            background-color: #e2e8f0;
            height: 1.5rem;
            border-radius: 0.75rem;
            overflow: hidden;
            margin: 1.5rem 0;
        }
        .progress-bar {
            height: 100%;
            background-color: #4f46e5;
            border-radius: 0.75rem;
            transition: width 0.3s ease;
        }
        .progress-pending {
            background-color: #94a3b8;
        }
        .progress-processing {
            background-color: #4f46e5;
        }
        .progress-completed {
            background-color: #10b981;
        }
        .progress-failed {
            background-color: #ef4444;
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
        .content-details {
            margin-top: 1.5rem;
        }
        .content-details dt {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        .content-details dd {
            margin-left: 0;
            margin-bottom: 1rem;
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
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-pending {
            background-color: #e2e8f0;
            color: #475569;
        }
        .badge-processing {
            background-color: #e0e7ff;
            color: #4f46e5;
        }
        .badge-completed {
            background-color: #d1fae5;
            color: #059669;
        }
        .badge-failed {
            background-color: #fee2e2;
            color: #dc2626;
        }
    </style>

    <!-- Refresh page every 15 seconds if status is 'pending' or 'processing' -->
    @if (isset($progress['status']) && in_array($progress['status'], ['pending', 'processing']))
        <meta http-equiv="refresh" content="15">
    @endif
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
            <a href="{{ route('content.processor') }}" class="tab-link">Process Content</a>
            <a href="{{ route('content.processor.list') }}" class="tab-link">View All Content</a>
            <a href="{{ route('content.processor.status', ['content' => $content->id]) }}" class="tab-link active">Content Status</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-title">
                Content Processing Status
                @php
                    $status = $progress['status'] ?? ($transcript && $transcript->processed ? 'completed' : 'pending');
                    $statusClass = match($status) {
                        'completed' => 'badge-completed',
                        'processing' => 'badge-processing',
                        'failed' => 'badge-failed',
                        default => 'badge-pending'
                    };
                @endphp
                <span class="badge {{ $statusClass }}">{{ ucfirst($status) }}</span>
            </div>

            <div class="progress-container">
                <div 
                    class="progress-bar progress-{{ $progress['status'] ?? 'pending' }}" 
                    style="width: {{ $percentage }}%;"
                ></div>
            </div>
            
            <p><strong>Progress:</strong> {{ $percentage }}%</p>
            
            @if (isset($progress['message']))
                <p><strong>Status Message:</strong> {{ $progress['message'] }}</p>
            @endif

            <div class="content-details">
                <dl>
                    <dt>Title</dt>
                    <dd>{{ $content->title }}</dd>

                    <dt>Source Type</dt>
                    <dd>{{ ucfirst($content->source_type) }}</dd>

                    <dt>Source URL</dt>
                    <dd><a href="{{ $content->source_url }}" target="_blank">{{ $content->source_url }}</a></dd>

                    <dt>Created At</dt>
                    <dd>{{ $content->created_at->format('F j, Y g:i a') }}</dd>

                    @if ($transcript)
                        <dt>Transcript</dt>
                        <dd>{{ $transcript->processed ? 'Processed' : 'Processing' }}</dd>
                        
                        <dt>Language</dt>
                        <dd>{{ strtoupper($transcript->language) }}</dd>
                        
                        @if ($transcript->processed)
                            <dt>Transcript Length</dt>
                            <dd>{{ number_format($transcript->token_count) }} tokens</dd>
                            
                            <dt>Chunks</dt>
                            <dd>{{ $transcript->chunks->count() }}</dd>
                            
                            <dt>Sample Content</dt>
                            <dd style="max-height: 200px; overflow-y: auto; padding: 10px; background: #f1f5f9; border-radius: 4px; font-size: 0.9rem;">
                                {{ Str::limit($transcript->full_text, 500) }}
                            </dd>
                        @endif
                    @endif
                </dl>
            </div>

            <div style="margin-top: 2rem;">
                <a href="{{ route('content.processor') }}" class="btn btn-secondary">Process Another URL</a>
                @if ($transcript && $transcript->processed)
                    <a href="{{ route('content.processor.list') }}" class="btn btn-primary">View All Content</a>
                @endif
            </div>
        </div>
    </div>

    @if (isset($progress['status']) && in_array($progress['status'], ['pending', 'processing']))
    <script>
        // Auto-refresh the page every 15 seconds if content is still processing
        setTimeout(function() {
            window.location.reload();
        }, 15000);
    </script>
    @endif
</body>
</html> 