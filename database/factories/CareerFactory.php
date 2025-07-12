<?php

namespace Database\Factories;

use App\Models\Career;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Career>
 */
class CareerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Career::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $careerNames = [
            // Ingeniería
            'Ingeniería de Sistemas',
            'Ingeniería Civil',
            'Ingeniería Industrial',
            'Ingeniería Electrónica',
            'Ingeniería Mecánica',
            'Ingeniería de Software',
            'Ingeniería Química',
            'Ingeniería Ambiental',

            // Ciencias
            'Licenciatura en Matemáticas',
            'Licenciatura en Física',
            'Licenciatura en Química',
            'Licenciatura en Biología',
            'Licenciatura en Estadística',

            // Medicina
            'Medicina General',
            'Enfermería',
            'Odontología',
            'Fisioterapia',
            'Nutrición y Dietética',

            // Otros
            'Administración de Empresas',
            'Contaduría Pública',
            'Derecho',
            'Psicología',
            'Comunicación Social',
            'Diseño Gráfico',
            'Arquitectura'
        ];

        $careerName = fake()->randomElement($careerNames);

        return [
            'department_id' => Department::factory(),
            'name' => $careerName,
            'code' => strtoupper(fake()->unique()->lexify('????')),
            'created_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'updated_by' => fake()->randomElement(['admin', 'system', 'test-user']),
            'version' => fake()->numberBetween(1, 3),
        ];
    }

    /**
     * Indicate that the career belongs to a specific department.
     */
    public function forDepartment(Department $department): static
    {
        return $this->state(fn(array $attributes) => [
            'department_id' => $department->id,
        ]);
    }

    /**
     * Create an engineering career.
     */
    public function engineering(): static
    {
        $engineeringCareers = [
            'Ingeniería de Sistemas',
            'Ingeniería Civil',
            'Ingeniería Industrial',
            'Ingeniería Electrónica',
            'Ingeniería Mecánica',
            'Ingeniería de Software'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => fake()->randomElement($engineeringCareers),
        ]);
    }

    /**
     * Create a sciences career.
     */
    public function sciences(): static
    {
        $sciencesCareers = [
            'Licenciatura en Matemáticas',
            'Licenciatura en Física',
            'Licenciatura en Química',
            'Licenciatura en Biología'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => fake()->randomElement($sciencesCareers),
        ]);
    }

    /**
     * Create a medicine career.
     */
    public function medicine(): static
    {
        $medicineCareers = [
            'Medicina General',
            'Enfermería',
            'Odontología',
            'Fisioterapia'
        ];

        return $this->state(fn(array $attributes) => [
            'name' => fake()->randomElement($medicineCareers),
        ]);
    }
}
