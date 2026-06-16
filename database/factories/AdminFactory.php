<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Admin;
use App\Support\Rbac;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->withRole(Rbac::SUPER_ADMIN);
    }

    public function admin(): static
    {
        return $this->withRole(Rbac::ADMIN);
    }

    public function branchManager(): static
    {
        return $this->withRole(Rbac::BRANCH_MANAGER);
    }

    /**
     * Assign a role after creation, creating it on the admin guard if absent so
     * factories work in isolation without seeding first.
     */
    public function withRole(string $role): static
    {
        return $this->afterCreating(function (Admin $admin) use ($role): void {
            $admin->assignRole(Role::findOrCreate($role, Rbac::GUARD));
        });
    }
}
