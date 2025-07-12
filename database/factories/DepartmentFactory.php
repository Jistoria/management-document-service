<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\HeadOffice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Department::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departmentName = fake()->randomElement([
            'Facultad de Ingeniería',
            'Facultad de Ciencias',
            'Facultad de Medicina',
            'Facultad de Derecho',
            'Facultad de Economía',
            'Facultad de Psicología',
            'Facultad de Educación',
            'Facultad de Arte',
            'Facultad de Historia',
            'Facultad de Filosofía',
            'Departamento de Matemáticas',
            'Departamento de Física',
            'Departamento de Química',
            'Departamento de Biología',
            'Departamento de Informática'
        ]);

        return [
            'head_office_id' => HeadOffice::factory(),
            'name' => $departmentName,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'created_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'updated_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'version' => fake()->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate that the department belongs to a specific head office.
     */
    public function forHeadOffice(HeadOffice $headOffice): static
    {
        return $this->state(fn(array $attributes) => [
            'head_office_id' => $headOffice->id,
        ]);
    }

    /**
     * Create a engineering department.
     */
    public function engineering(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Facultad de Ingeniería',
            'code' => 'ING',
        ]);
    }

    /**
     * Create a sciences department.
     */
    public function sciences(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Facultad de Ciencias',
            'code' => 'CIEN',
        ]);
    }

    /**
     * Create a medicine department.
     */
    public function medicine(): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => 'Facultad de Medicina',
            'code' => 'MED',
        ]);
    }
}
