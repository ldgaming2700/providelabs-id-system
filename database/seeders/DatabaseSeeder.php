<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CardTypeSeeder::class);

        User::updateOrCreate(
            ['email' => env('DEFAULT_ADMIN_EMAIL', 'admin@providelabscorp.com')],
            [
                'name' => 'ProvideLabs Admin',
                'password' => Hash::make(env('DEFAULT_ADMIN_PASSWORD', 'ChangeMeNow!')),
                'role' => 'admin',
            ]
        );
    }
}
