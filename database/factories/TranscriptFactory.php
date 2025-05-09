<?php
// [ai-generated-code]

namespace Database\Factories;

use App\Models\Content;
use App\Models\Transcript;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranscriptFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transcript::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content_id' => Content::factory(),
            'full_text' => $this->faker->paragraphs(5, true),
            'language' => 'en',
            'source_url' => $this->faker->url,
            'duration_seconds' => $this->faker->numberBetween(60, 3600),
            'token_count' => $this->faker->numberBetween(100, 5000),
            'metadata' => ['source_type' => 'youtube', 'processed_at' => now()->toIso8601String()],
            'processed' => true,
        ];
    }

    /**
     * Indicate that the transcript is not processed.
     *
     * @return self
     */
    public function unprocessed(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'processed' => false,
            ];
        });
    }

    /**
     * Indicate that the transcript is for a YouTube video.
     *
     * @return self
     */
    public function youtube(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'metadata' => ['source_type' => 'youtube', 'processed_at' => now()->toIso8601String()],
            ];
        });
    }

    /**
     * Indicate that the transcript is for an article.
     *
     * @return self
     */
    public function article(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'metadata' => ['source_type' => 'article', 'processed_at' => now()->toIso8601String()],
            ];
        });
    }
} 