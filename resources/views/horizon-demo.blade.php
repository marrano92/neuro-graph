<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Horizon Demo</title>
    <!-- Include Tailwind via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans antialiased">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 mt-8">
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 px-6 py-4">
                <h3 class="text-xl font-semibold text-white">Laravel Horizon Demo</h3>
            </div>
            <div class="p-6">
                @if (session('status'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white border rounded-lg shadow-sm h-full">
                        <div class="p-5">
                            <h5 class="text-lg font-medium mb-2">Dispatch Jobs</h5>
                            <p class="text-gray-600 mb-4">Submit this form to dispatch example jobs to the queue for processing by Horizon.</p>
                            <form action="{{ route('horizon.dispatch') }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="count" class="block text-sm font-medium text-gray-700 mb-1">Number of Jobs</label>
                                    <input type="number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" 
                                        id="count" name="count" value="10" min="1" max="100">
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded shadow-sm">
                                    Dispatch Jobs
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="bg-white border rounded-lg shadow-sm h-full">
                        <div class="p-5">
                            <h5 class="text-lg font-medium mb-2">Horizon Dashboard</h5>
                            <p class="text-gray-600 mb-4">Visit the Horizon dashboard to monitor queue processing, job metrics, and more.</p>
                            <a href="{{ url('/horizon') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded shadow-sm inline-block" target="_blank">
                                Open Horizon Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white border rounded-lg shadow-sm">
                    <div class="p-5">
                        <h5 class="text-lg font-medium mb-2">How it works</h5>
                        <p class="mb-2">This demo dispatches <code class="bg-gray-100 px-1 py-0.5 rounded text-sm">ExampleJob</code> instances to a Redis queue. The jobs are then processed by Laravel Horizon.</p>
                        <p class="mb-2">Each job simulates work by sleeping for 2 seconds and then logging a message.</p>
                        <p class="mb-2">Check the Horizon dashboard to see:</p>
                        <ul class="list-disc pl-5 text-gray-600">
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