<?php
// [ai-generated-code]

namespace Database\Factories;

use App\Models\Content;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Content>
 */
class ContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Content::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sourceTypes = ['Video', 'Article', 'Book', 'Paper', 'Podcast', 'Course', 'Website'];
        
        return [
            'title' => $this->faker->sentence(4),
            'source_type' => $this->faker->randomElement($sourceTypes),
            'source_url' => $this->faker->url(),
            'summary' => $this->faker->paragraph(3),
        ];
    }
} 