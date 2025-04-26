<?php

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
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->string('label'); // Name/short label of the concept
            $table->text('description')->nullable(); // More detailed description
            $table->string('type'); // Type of concept (Technology, Person, Theory, etc.)
            $table->string('source')->nullable(); // Origin of the concept (YouTube, Blog, Book)
            $table->json('embedding')->nullable(); // Semantic vector for similarity comparisons
            $table->string('color')->nullable(); // Optional color for visual differentiation
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodes');
    }
};
