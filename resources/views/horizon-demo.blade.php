<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Horizon Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8fafc;
        }
        .container {
            max-width: 800px;
            margin-top: 2rem;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">Laravel Horizon Demo</h3>
            </div>
            <div class="card-body">
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Dispatch Jobs</h5>
                                <p class="card-text">Submit this form to dispatch example jobs to the queue for processing by Horizon.</p>
                                <form action="{{ route('horizon.dispatch') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="count" class="form-label">Number of Jobs</label>
                                        <input type="number" class="form-control" id="count" name="count" value="10" min="1" max="100">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Dispatch Jobs</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Horizon Dashboard</h5>
                                <p class="card-text">Visit the Horizon dashboard to monitor queue processing, job metrics, and more.</p>
                                <a href="{{ url('/horizon') }}" class="btn btn-success" target="_blank">Open Horizon Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">How it works</h5>
                        <p>This demo dispatches <code>ExampleJob</code> instances to a Redis queue. The jobs are then processed by Laravel Horizon.</p>
                        <p>Each job simulates work by sleeping for 2 seconds and then logging a message.</p>
                        <p>Check the Horizon dashboard to see:</p>
                        <ul>
                            <li>Real-time job processing metrics</li>
                            <li>Queue workload distribution</li>
                            <li>Failed jobs (if any)</li>
                            <li>Process counts and throughput</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 