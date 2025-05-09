<?php
// [ai-generated-code]

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transcript_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transcript_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('text');
            $table->float('start_time')->nullable();
            $table->float('end_time')->nullable();
            $table->unsignedInteger('token_count')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('chunk_index');
            $table->index(['transcript_id', 'chunk_index']);
            
            // Full-text index for semantic search
            $table->fullText('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_chunks');
    }
}; 