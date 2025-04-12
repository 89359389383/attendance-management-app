<?php

use Illuminate\Support\Str;

$factory->define(App\Models\User::class, function () {
    return [
        'name' => fake()->name,
        'email' => fake()->unique()->safeEmail,
        'password' => bcrypt('password123'),
        'is_admin' => false,
    ];
});
