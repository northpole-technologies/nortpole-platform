<?php

declare(strict_types=1);

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Customer;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'organisation_id' => null,
            'type' => 'individual',
            'status' => 'active',
            'name' => fake()->name(),
            'company_name' => null,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'mobile' => fake()->phoneNumber(),
            'website' => fake()->optional()->url(),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => fake()->optional()->secondaryAddress(),
            'city' => fake()->city(),
            'county' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country' => 'IE',
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function company(): self
    {
        return $this->state(fn (): array => [
            'type' => 'company',
            'name' => fake()->company(),
            'company_name' => fake()->company(),
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
        ]);
    }
}
