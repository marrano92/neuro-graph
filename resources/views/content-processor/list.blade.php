<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - Content List</title>

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
        .alert {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
        }
        .alert-success {
            color: #0f766e;
            background-color: #ccfbf1;
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
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            font-weight: 600;
            color: #475569;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .truncate {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 2rem 0;
            padding: 0;
        }
        .pagination li {
            margin: 0 0.25rem;
        }
        .pagination a, .pagination span {
            display: inline-block;
            padding: 0.5rem 0.75rem;
            border-radius: 0.25rem;
            text-decoration: none;
            color: #4f46e5;
            background-color: white;
            border: 1px solid #e2e8f0;
        }
        .pagination a:hover {
            background-color: #f1f5f9;
        }
        .pagination .active span {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
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
            <a href="{{ route('content.processor') }}" class="tab-link">Process Content</a>
            <a href="{{ route('content.processor.list') }}" class="tab-link active">View All Content</a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card">
            <div class="card-title">All Content</div>
            
            @if ($contents->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Source Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($contents as $content)
                            <tr>
                                <td>{{ $content->id }}</td>
                                <td class="truncate">{{ $content->title }}</td>
                                <td>{{ ucfirst($content->source_type) }}</td>
                                <td>
                                    @php
                                        $status = 'pending';
                                        $badgeClass = 'badge-pending';
                                        
                                        if ($content->transcript) {
                                            $status = $content->transcript->processed ? 'completed' : 'processing';
                                            $badgeClass = $content->transcript->processed ? 'badge-completed' : 'badge-processing';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td>{{ $content->created_at->format('M j, Y') }}</td>
                                <td>
                                    <a href="{{ route('content.processor.status', ['content' => $content->id]) }}" class="btn btn-secondary">View Status</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <div class="pagination-container">
                    {{ $contents->links() }}
                </div>
            @else
                <p>No content has been processed yet.</p>
                <a href="{{ route('content.processor') }}" class="btn btn-primary">Process Content</a>
            @endif
        </div>
    </div>
</body>
</html> 