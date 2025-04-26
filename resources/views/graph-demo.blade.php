<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Neuro Graph Demo</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* [ai-generated-code] */
        #cy {
            width: 100%;
            height: 600px;
            background-color: #f9fafb;
            margin-top: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .controls {
            padding: 20px;
            background-color: #f3f4f6;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .controls button {
            background-color: #6366f1;
            color: white;
            padding: 8px 16px;
            border-radius: 4px;
            border: none;
            margin-right: 10px;
            cursor: pointer;
        }
        .controls button:hover {
            background-color: #4f46e5;
        }
    </style>
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-100">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-semibold mb-4">Neuro Graph Demo</h1>
                        
                        <div class="controls">
                            <button id="add-node">Add Node</button>
                            <button id="add-edge">Add Edge</button>
                            <button id="reset">Reset Graph</button>
                        </div>
                        
                        <div id="cy"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // [ai-generated-code]
        document.addEventListener('DOMContentLoaded', function() {
            // Sample graph data
            const initialData = {
                nodes: [
                    { id: 'node1', label: 'Neuron 1' },
                    { id: 'node2', label: 'Neuron 2' },
                    { id: 'node3', label: 'Neuron 3' }
                ],
                edges: [
                    { source: 'node1', target: 'node2', label: 'Synaptic connection' },
                    { source: 'node2', target: 'node3', label: 'Inhibitory' }
                ]
            };

            // Initialize the graph
            const cy = window.initCytoscapeGraph('cy', initialData);
            
            // Node counter for adding new nodes
            let nodeCount = initialData.nodes.length;
            
            // Add node button handler
            document.getElementById('add-node').addEventListener('click', function() {
                nodeCount++;
                const newNodeId = `node${nodeCount}`;
                cy.add({
                    group: 'nodes',
                    data: { 
                        id: newNodeId,
                        label: `Neuron ${nodeCount}`
                    },
                    position: {
                        x: 100 + Math.random() * 200,
                        y: 100 + Math.random() * 200
                    }
                });
            });
            
            // Add edge button handler
            document.getElementById('add-edge').addEventListener('click', function() {
                // Get all nodes
                const nodes = cy.nodes().map(node => node.id());
                
                if (nodes.length < 2) return;
                
                // Select two random nodes
                const sourceIndex = Math.floor(Math.random() * nodes.length);
                let targetIndex;
                do {
                    targetIndex = Math.floor(Math.random() * nodes.length);
                } while (targetIndex === sourceIndex);
                
                const sourceId = nodes[sourceIndex];
                const targetId = nodes[targetIndex];
                const edgeId = `${sourceId}-${targetId}`;
                
                // Check if edge already exists
                if (!cy.getElementById(edgeId).length) {
                    cy.add({
                        group: 'edges',
                        data: {
                            id: edgeId,
                            source: sourceId,
                            target: targetId,
                            label: 'Connection'
                        }
                    });
                }
            });
            
            // Reset button handler
            document.getElementById('reset').addEventListener('click', function() {
                cy.elements().remove();
                nodeCount = 0;
                
                // Re-add initial nodes and edges
                initialData.nodes.forEach(node => {
                    cy.add({
                        group: 'nodes',
                        data: { 
                            id: node.id,
                            label: node.label
                        }
                    });
                    nodeCount++;
                });
                
                initialData.edges.forEach(edge => {
                    cy.add({
                        group: 'edges',
                        data: {
                            id: `${edge.source}-${edge.target}`,
                            source: edge.source,
                            target: edge.target,
                            label: edge.label || ''
                        }
                    });
                });
                
                cy.layout({ name: 'cose' }).run();
            });
        });
    </script>
</body>
</html> 