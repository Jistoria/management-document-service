<?php

namespace Database\Factories;

use App\Models\HeadOffice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HeadOffice>
 */
class HeadOfficeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HeadOffice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $officeName = fake()->randomElement([
            'Sede Central',
            'Sede Norte',
            'Sede Sur',
            'Sede Este',
            'Sede Oeste',
            'Campus Principal',
            'Campus Universitario',
            'Sede Regional',
            'Centro Académico',
            'Complejo Educativo'
        ]);

        return [
            'name' => $officeName,
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'created_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'updated_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'version' => fake()->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate that the head office is the main campus.
     */
    public function mainCampus(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Sede Central',
            'code' => 'CENTRAL',
        ]);
    }

    /**
     * Indicate that the head office is a regional campus.
     */
    public function regional(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Sede Regional ' . fake()->city(),
            'code' => 'REG' . fake()->randomNumber(2),
        ]);
    }
}
