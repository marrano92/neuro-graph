import cytoscape from 'cytoscape';

/**
 * Initialize a Cytoscape graph instance
 * @param {string} containerId - The ID of the container element
 * @param {Object} data - The graph data with nodes and edges
 * @returns {Object} - The Cytoscape instance
 */
export function initCytoscapeGraph(containerId, data = { nodes: [], edges: [] }) {
    const defaultStyle = [
        {
            selector: 'node',
            style: {
                'background-color': '#6366f1',
                'label': 'data(label)',
                'color': '#000000',
                'text-valign': 'center',
                'text-halign': 'center',
                'width': 50,
                'height': 50
            }
        },
        {
            selector: 'edge',
            style: {
                'width': 2,
                'line-color': '#9ca3af',
                'target-arrow-color': '#9ca3af',
                'target-arrow-shape': 'triangle',
                'curve-style': 'bezier',
                'label': 'data(label)',
                'text-rotation': 'autorotate'
            }
        }
    ];

    // Initialize Cytoscape instance
    const cy = cytoscape({
        container: document.getElementById(containerId),
        elements: {
            nodes: data.nodes.map(node => ({
                data: { 
                    id: node.id,
                    label: node.label || node.id
                }
            })),
            edges: data.edges.map(edge => ({
                data: {
                    id: edge.id || `${edge.source}-${edge.target}`,
                    source: edge.source,
                    target: edge.target,
                    label: edge.label || ''
                }
            }))
        },
        style: defaultStyle,
        layout: {
            name: 'cose',
            padding: 50
        }
    });

    return cy;
}

/**
 * Add nodes to an existing Cytoscape instance
 * @param {Object} cy - The Cytoscape instance
 * @param {Array} nodes - Array of nodes to add
 */
export function addNodes(cy, nodes) {
    nodes.forEach(node => {
        if (!cy.getElementById(node.id).length) {
            cy.add({
                group: 'nodes',
                data: { 
                    id: node.id,
                    label: node.label || node.id
                }
            });
        }
    });
    cy.layout({ name: 'cose' }).run();
}

/**
 * Add edges to an existing Cytoscape instance
 * @param {Object} cy - The Cytoscape instance
 * @param {Array} edges - Array of edges to add
 */
export function addEdges(cy, edges) {
    edges.forEach(edge => {
        const edgeId = edge.id || `${edge.source}-${edge.target}`;
        if (!cy.getElementById(edgeId).length) {
            cy.add({
                group: 'edges',
                data: {
                    id: edgeId,
                    source: edge.source,
                    target: edge.target,
                    label: edge.label || ''
                }
            });
        }
    });
} 