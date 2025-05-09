# YouTube Transcript Processing System

This module implements a robust system for processing, storing, and analyzing YouTube video transcripts as part of the Neuro-Graph application.

## Overview

The system is designed to:

1. Store full transcripts from YouTube videos
2. Break down transcripts into manageable chunks for precise retrieval
3. Support semantic search via vector embeddings
4. Provide time-based navigation of video content

## Key Components

### Data Models

- **Transcript**: Stores the complete transcript text and metadata
- **TranscriptChunk**: Stores individual segments of the transcript with time markers and embeddings

### Features

- Full-text search across all transcripts
- Vector similarity search for semantic matching
- Time-based retrieval for video navigation
- Relationship to the Content model for integration with the knowledge graph

## Implementation Details

### Database Schema

The implementation includes:

- Two new database tables: `transcripts` and `transcript_chunks`
- Full-text indexes for textual search
- Support for vector embeddings (JSON format by default with flexibility for pgvector)
- Comprehensive relationships between models

### Performance Optimizations

The system includes several optimizations:

- Strategic database indexing for common query patterns
- Chunking strategy for efficient retrieval and processing
- Time-based navigation methods
- Prepared for vector similarity search

## Usage Examples

### Basic Operations

```php
// Get a transcript for a Content item
$transcript = Content::find($id)->transcript;

// Get all chunks for a transcript
$chunks = $transcript->chunks;

// Get a chunk at a specific time in the video
$chunk = $transcript->getChunkByTime(120.5); // 2 minutes, 0.5 seconds

// Get chunks within a time range
$chunks = $transcript->getChunksInTimeRange(60, 120); // 1-2 minute mark
```

### Semantic Search

```php
// Find chunks semantically similar to a query
$embedding = YourEmbeddingService::embedText($query);
$similarChunks = TranscriptChunk::findSimilar($embedding, 5);
```

## Technical Requirements

- Laravel Scout for search functionality
- Database with full-text search capability
- Optional: PostgreSQL with pgvector extension for optimal vector search

## Testing

The system includes comprehensive unit tests that validate:

- Basic CRUD operations
- Relationship integrity
- Time-based retrieval methods
- Vector similarity search

Run the tests with:

```bash
./vendor/bin/sail artisan test --filter=TranscriptTest
```

## Future Roadmap

1. Implement a dedicated service for YouTube transcript extraction
2. Add support for auto-chunking based on semantic boundaries
3. Optimize vector storage for large-scale deployments
4. Add support for transcript highlighting and annotation

## Contributing

When extending the transcript system, please follow these guidelines:

1. Maintain the chunking strategy for large transcripts
2. Ensure all new features have corresponding tests
3. Consider vector search performance in your implementation 