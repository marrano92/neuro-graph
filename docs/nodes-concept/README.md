# ConceptMapperAI

ConceptMapperAI is a Laravel-based application that builds an **interactive graph of concepts** extracted from various sources (YouTube videos, articles, etc.). The system automatically **analyzes**, **relates**, and **merges** concepts based on semantic similarity, creating a **unified map of knowledge**.

---

## 📈 Overview

- **Content** (source of information) becomes the **main node**.
- **Concepts** extracted from the content become **child nodes**.
- Concepts are **not simple words**: they are **key ideas or rich semantic constructs**.
- When new content is uploaded:
    - Concepts are extracted.
    - If a new concept is **similar** to an existing one, the system **merges** them.
    - The merged concept is linked to **both sources**.

---

## 🔠 Core Models

### Concept (Node)
Represents a key concept extracted from a source.

**Attributes:**
- `id`: unique identifier.
- `label`: short name of the concept.
- `description`: extended explanation.
- `type`: type/category (e.g., "Technology", "Theory").
- `source`: original content source (e.g., "YouTube", "Article").
- `embedding`: JSON array representing the semantic vector.
- `importance_score`: (float) how central the concept is (0.0 - 1.0).
- `color`: optional for visualization.

### Content (Main Node)
Represents a loaded source of information.

**Attributes:**
- `id`: unique identifier.
- `title`: title of the content.
- `source_type`: type of source (e.g., Video, Article).
- `source_url`: URL or reference.
- `summary`: brief description.

**Relationships:**
- Many-to-Many with `Concept` via `content_concept` pivot table.

### Relationship (Edge)
Represents a semantic connection between two concepts.

**Attributes:**
- `id`: unique identifier.
- `source_id`: source concept.
- `target_id`: target concept.
- `weight`: numerical value indicating strength of relation.
- `type`: description of relationship (e.g., "is part of", "associated with").
- `directed`: whether the relationship has a direction.
- `description`: optional explanation.

---

## 🔬 Semantic Similarity and Merging Logic

When new concepts are extracted:
1. **Embedding Generation**:
    - Each concept is transformed into a **semantic vector** using:
        - **OpenAI Embeddings** (`text-embedding-ada-002`, paid, highly accurate)
        - or **Sentence Transformers** (`all-MiniLM-L6-v2`, open-source, free)
2. **Cosine Similarity Calculation**:
    - Measures how "aligned" two vectors are.
    - Formula:
      \[\text{similarity}(A,B) = \frac{A \cdot B}{\|A\| \times \|B\|}\]
    - Values close to `1` mean "very similar".
3. **Decision**:
    - If similarity > **threshold** (e.g., `0.85`):
        - **Do not create a new node**.
        - **Link the new content** to the existing concept.
    - Otherwise:
        - **Create a new concept** node.

---

## 💍 Importance Score

Each concept has an `importance_score`:
- Float value (0.0 to 1.0).
- Indicates how **central** the concept is to the content.
- Used for:
    - Prioritizing node size in visualization.
    - Filtering less relevant concepts.
    - Simplifying large graphs.

Scoring Methods:
- Number of times mentioned.
- Contextual importance (e.g., appears in introduction/conclusion).
- Weighted AI-based estimation.

---

## 💡 Infrastructure Summary

- **Backend**: Laravel 11
- **Queue System**: Laravel Horizon + Redis
- **Authentication**: Laravel Sanctum (API tokens)
- **Embedding Generation**: OpenAI API (preferred) or Sentence Transformers
- **Database**: PostgreSQL with optional `pgvector` extension for efficient similarity search
- **Frontend**: Vue 3 + Cytoscape.js for interactive graph visualization

---

## 🔍 Graph Visualization (Cytoscape.js)

- **Nodes**:
    - Size proportional to `importance_score`.
    - Label from `label`.
- **Edges**:
    - Thickness proportional to `weight`.
    - Label from `type` or `weight`.
- **Layout**:
    - Breadthfirst (tree structure) or Force-directed for organic graphs.

---

## 📣 Future Improvements

- Dynamic real-time updates via Websockets.
- Contextual search and filtering inside the graph.
- Visual clustering of concepts by domain/topic.

---

# 🚀 Quick Summary

ConceptMapperAI is a powerful, dynamic platform for building semantic maps of knowledge that grow naturally as new information is added, intelligently merging concepts to avoid duplication and create a unified learning experience.

