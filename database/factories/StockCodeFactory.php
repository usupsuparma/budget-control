<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StockCode>
 */
class StockCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stock_code' => $this->faker->unique()->bothify('STK-####??'),
            'name' => $this->faker->words(3, true),
            'unit' => $this->faker->randomElement(['PCS', 'BOX', 'KG', 'LITER', 'SET']),
            'budget_code' => $this->faker->bothify('BDG-####'),
            'active' => '1',
            'warehouse' => $this->faker->randomElement(['MAIN', 'SUB', 'TRANSIT']),
            'category' => $this->faker->randomElement(['RAW MATERIAL', 'SPAREPART', 'STATIONARY', 'PROMOTIONAL']),
            'product_line' => $this->faker->randomElement(['LINE A', 'LINE B', 'LINE C']),
        ];
    }
}
