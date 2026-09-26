<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<AuditLog> */
class AuditLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'action' => 'test.created',
            'subject_type' => 'page',
            'subject_id' => 1,
            'changes' => ['title' => ['old' => null, 'new' => 'About us']],
            'request_id' => (string) Str::uuid(),
        ];
    }
}
