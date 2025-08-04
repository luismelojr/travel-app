<?php

namespace Database\Factories;

use App\Enums\TravelRequestStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TravelRequest>
 */
class TravelRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departureDate = $this->faker->dateTimeBetween('+1 week', '+3 months');
        $returnDate = clone $departureDate;
        $returnDate->modify('+' . $this->faker->numberBetween(1, 30) . ' days');

        return [
            'user_id' => User::factory(),
            'requester_name' => $this->faker->name(),
            'destination' => $this->faker->city() . ', ' . $this->faker->country(),
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'status' => TravelRequestStatusEnum::REQUESTED,
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the travel request is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatusEnum::APPROVED,
        ]);
    }

    /**
     * Indicate that the travel request is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TravelRequestStatusEnum::CANCELLED,
        ]);
    }

    /**
     * Indicate that the travel request has specific dates.
     */
    public function withDates(\DateTime $departureDate, \DateTime $returnDate): static
    {
        return $this->state(fn (array $attributes) => [
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
        ]);
    }
}
