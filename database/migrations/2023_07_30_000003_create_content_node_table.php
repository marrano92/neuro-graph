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
        Schema::create('content_node', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->onDelete('cascade');
            $table->foreignId('node_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Create a unique index to prevent duplicate associations
            $table->unique(['content_id', 'node_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_node');
    }
}; 