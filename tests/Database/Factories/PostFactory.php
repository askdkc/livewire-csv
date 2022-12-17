<?php

namespace Askdkc\LivewireCsv\Tests\Database\Factories;

use Askdkc\LivewireCsv\Tests\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(20),
            'slug' => $this->faker->unique()->sentence(20),
            'body' => $this->faker->text(),
            'extra' => $this->faker->sentence(),
        ];
    }
}
