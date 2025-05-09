# Transcript Data Model Documentation

This document outlines the implementation of the transcript data model for storing and analyzing YouTube video transcriptions.

## Overview

The transcript system is designed to handle large transcriptions from various sources (primarily YouTube videos) in a way that enables efficient storage, retrieval, and semantic analysis. The system implements a chunking strategy to break down large transcripts into manageable pieces that can be independently analyzed and retrieved.

## Data Models

### 1. Transcript Model

The `Transcript` model stores the full transcription text and metadata related to the source.

**Key fields:**
- `content_id`: Foreign key relationship to the Content model
- `full_text`: The complete transcription text (with fulltext indexing)
- `language`: The language of the transcript (default: 'en')
- `duration_seconds`: Duration of the source media in seconds
- `token_count`: Number of tokens in the transcript
- `source_url`: URL of the original source
- `metadata`: JSON field for additional metadata
- `processed`: Boolean flag indicating whether the transcript has been processed

**Key relationships:**
- Belongs to one `Content` record
- Has many `TranscriptChunk` records

**Key features:**
- Full-text search capability via Laravel Scout
- Helper methods to retrieve chunks by time position

### 2. TranscriptChunk Model

The `TranscriptChunk` model represents smaller segments of the transcript (300-500 words), making it possible to precisely locate and retrieve specific parts of the transcript.

**Key fields:**
- `transcript_id`: Foreign key relationship to the Transcript model
- `chunk_index`: The sequential index of this chunk
- `text`: The text content of this chunk
- `start_time`: Start time of this chunk in the source media (in seconds)
- `end_time`: End time of this chunk in the source media (in seconds)
- `token_count`: Number of tokens in this chunk
- `embedding`: JSON array storing the vector embedding for semantic search

**Key relationships:**
- Belongs to one `Transcript` record

**Key features:**
- Fulltext search on chunk text
- Vector similarity search capability (implementation dependent on database)
- Time-based retrieval methods

## Performance Optimizations

### 1. Database Indexing

The implementation includes strategic indexes for optimal performance:

- Full-text indexes on `Transcript.full_text` and `TranscriptChunk.text`
- Regular indexes on foreign keys and frequently queried fields
- Compound indexes for common query patterns

### 2. Vector Search

The system is designed to support vector similarity search for semantic matching:

- JSON storage for embeddings by default
- Placeholder code for pgvector implementation with PostgreSQL
- Support for finding similar chunks based on embedding similarity

### 3. Chunk-Level Retrieval

The chunking strategy allows for precise retrieval of transcript portions:

- Time-based retrieval (get chunk at specific timestamp)
- Range-based retrieval (get chunks within a time range)
- Semantic similarity retrieval (find chunks similar to a given embedding)

## Usage Recommendations

1. **Database Choice:**
   - For production, consider using PostgreSQL with the pgvector extension for efficient vector similarity search

2. **Chunking Strategy:**
   - Aim for 300-500 word chunks for optimal balance between context and specificity
   - Consider semantic boundaries (paragraphs, sentences) when chunking

3. **Vector Embeddings:**
   - Use a consistent embedding model across all chunks
   - Store dimensionality information in the transcript metadata

4. **Scaling Considerations:**
   - For very large collections, consider specialized vector databases
   - Use background processing for embedding generation and chunking

## Example Use Cases

1. **Semantic Search:**
   ```php
   // Find chunks similar to a query
   $embedding = YourEmbeddingService::embedText($query);
   $similarChunks = TranscriptChunk::findSimilar($embedding, 5);
   ```

2. **Time-Based Retrieval:**
   ```php
   // Get transcript chunk at a specific timestamp
   $chunk = $transcript->getChunkByTime(135.5); // 2:15 into the video
   ```

3. **Full-Text Search:**
   ```php
   // Search across all transcripts
   $results = Transcript::search('specific topic')->get();
   ```

## Future Enhancements

1. Implement a dedicated service for transcript processing and chunking
2. Add support for multiple embedding models
3. Optimize chunk size dynamically based on content
4. Integrate with specialized vector databases for larger collections 