<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        uses(RefreshDatabase::class);

        if (!User::where('email', config('app.default_user.email'))->exists()) {
            $this->actingAs(User::factory()->create([
            'email' => config('app.default_user.email'),
            'password' => config('app.default_user.password'),
            ])->assignRole('super_admin'));
        } else {
            $this->actingAs(User::where('email', config('app.default_user.email'))->first()->assignRole('super_admin'));
        }

        $this->withoutVite();
    }
}
