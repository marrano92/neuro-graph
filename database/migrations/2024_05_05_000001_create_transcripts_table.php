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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->nullable()->constrained()->nullOnDelete();
            $table->text('full_text')->fulltext(); // Using fulltext index for semantic search
            $table->string('language', 10)->default('en');
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('token_count')->nullable();
            $table->string('source_url')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamps();
            
            // Index for performance
            $table->index('language');
            $table->index('processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
}; 