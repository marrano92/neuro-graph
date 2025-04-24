<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Scout with Meilisearch - Search Example</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h1 class="text-2xl font-semibold mb-6">Laravel Scout with Meilisearch Demo</h1>
                
                <!-- Search Form -->
                <div class="mb-8">
                    <form id="searchForm" class="space-y-4">
                        <div>
                            <label for="query" class="block text-sm font-medium text-gray-700">Search Query</label>
                            <input type="text" id="query" name="query" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Start typing to search...">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Search In</label>
                            <div class="mt-2 space-x-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" name="searchType" value="all" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" checked>
                                    <span class="ml-2">All</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="searchType" value="users" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <span class="ml-2">Users</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" name="searchType" value="nodes" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <span class="ml-2">Nodes</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="node-filters hidden">
                            <label class="block text-sm font-medium text-gray-700">Node Type Filter</label>
                            <select id="nodeType" name="nodeType" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="">All Types</option>
                                <option value="text">Text</option>
                                <option value="image">Image</option>
                                <option value="file">File</option>
                            </select>
                        </div>
                    </form>
                </div>
                
                <!-- Results Section -->
                <div class="space-y-6">
                    <div class="users-results hidden">
                        <h2 class="text-lg font-medium text-gray-900">Users Results</h2>
                        <div id="usersResults" class="mt-3 bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 italic">No results yet</p>
                        </div>
                    </div>
                    
                    <div class="nodes-results hidden">
                        <h2 class="text-lg font-medium text-gray-900">Nodes Results</h2>
                        <div id="nodesResults" class="mt-3 bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-500 italic">No results yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchForm = document.getElementById('searchForm');
            const queryInput = document.getElementById('query');
            const nodeFilters = document.querySelector('.node-filters');
            const usersResults = document.querySelector('.users-results');
            const nodesResults = document.querySelector('.nodes-results');
            const searchTypeRadios = document.querySelectorAll('input[name="searchType"]');
            
            // Toggle filters visibility based on search type
            searchTypeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'nodes') {
                        nodeFilters.classList.remove('hidden');
                    } else {
                        nodeFilters.classList.add('hidden');
                    }
                });
            });
            
            // Perform search on input change
            queryInput.addEventListener('input', debounce(performSearch, 300));
            document.getElementById('nodeType').addEventListener('change', performSearch);
            
            function performSearch() {
                const query = queryInput.value.trim();
                if (query.length < 1) {
                    resetResults();
                    return;
                }
                
                const searchType = document.querySelector('input[name="searchType"]:checked').value;
                const nodeType = document.getElementById('nodeType').value;
                
                let url = '';
                const params = new URLSearchParams();
                params.append('query', query);
                
                if (searchType === 'all') {
                    url = '/search';
                    showBothResultSections();
                } else if (searchType === 'users') {
                    url = '/search/users';
                    showOnlyUsersResults();
                } else if (searchType === 'nodes') {
                    url = '/search/nodes';
                    showOnlyNodesResults();
                    
                    if (nodeType) {
                        params.append('filters[type]', nodeType);
                    }
                }
                
                fetch(`${url}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        displayResults(data, searchType);
                    })
                    .catch(error => {
                        console.error('Error fetching search results:', error);
                    });
            }
            
            function displayResults(data, searchType) {
                if (searchType === 'all') {
                    displayUsersResults(data.users);
                    displayNodesResults(data.nodes);
                } else if (searchType === 'users') {
                    displayUsersResults(data.results);
                } else if (searchType === 'nodes') {
                    displayNodesResults(data.results);
                }
            }
            
            function displayUsersResults(users) {
                const usersResultsDiv = document.getElementById('usersResults');
                
                if (!users || users.length === 0) {
                    usersResultsDiv.innerHTML = '<p class="text-gray-500 italic">No users found</p>';
                    return;
                }
                
                let html = '<div class="space-y-3">';
                
                users.forEach(user => {
                    html += `
                        <div class="border rounded-md p-3 bg-white">
                            <div class="font-medium">${user.name}</div>
                            <div class="text-sm text-gray-500">${user.email}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
                usersResultsDiv.innerHTML = html;
            }
            
            function displayNodesResults(nodes) {
                const nodesResultsDiv = document.getElementById('nodesResults');
                
                if (!nodes || nodes.length === 0) {
                    nodesResultsDiv.innerHTML = '<p class="text-gray-500 italic">No nodes found</p>';
                    return;
                }
                
                let html = '<div class="space-y-3">';
                
                nodes.forEach(node => {
                    html += `
                        <div class="border rounded-md p-3 bg-white">
                            <div class="font-medium">${node.name}</div>
                            <div class="text-xs text-gray-400 uppercase">${node.type}</div>
                            <div class="text-sm mt-2 text-gray-700">${truncate(node.content, 150)}</div>
                        </div>
                    `;
                });
                
                html += '</div>';
                nodesResultsDiv.innerHTML = html;
            }
            
            function showBothResultSections() {
                usersResults.classList.remove('hidden');
                nodesResults.classList.remove('hidden');
            }
            
            function showOnlyUsersResults() {
                usersResults.classList.remove('hidden');
                nodesResults.classList.add('hidden');
            }
            
            function showOnlyNodesResults() {
                usersResults.classList.add('hidden');
                nodesResults.classList.remove('hidden');
            }
            
            function resetResults() {
                document.getElementById('usersResults').innerHTML = '<p class="text-gray-500 italic">No results yet</p>';
                document.getElementById('nodesResults').innerHTML = '<p class="text-gray-500 italic">No results yet</p>';
                usersResults.classList.add('hidden');
                nodesResults.classList.add('hidden');
            }
            
            function truncate(str, length) {
                if (!str) return '';
                return str.length > length ? str.substring(0, length) + '...' : str;
            }
            
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        func.apply(context, args);
                    }, wait);
                };
            }
        });
    </script>
</body>
</html> 