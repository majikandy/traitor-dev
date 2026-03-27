<?php

namespace Database\Factories;

use App\Models\Organisation;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SiteFactory extends Factory
{
    protected $model = Site::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name'            => $name,
            'slug'            => Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'organisation_id' => Organisation::factory(),
            'status'          => 'active',
        ];
    }
}
