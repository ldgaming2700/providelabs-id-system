<?php

namespace Database\Seeders;

use App\Models\CardType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CardTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Senior Citizen Card', 'front_title' => 'SENIOR CITIZEN CARD', 'back_title' => 'EMERGENCY INFORMATION', 'primary_color' => '#DE6900', 'secondary_color' => '#63C7D1'],
            ['name' => 'Officer ID', 'front_title' => 'OFFICER ID', 'back_title' => 'OFFICER INFORMATION', 'primary_color' => '#1A1A1A', 'secondary_color' => '#DE6900'],
            ['name' => 'Volunteer ID', 'front_title' => 'VOLUNTEER ID', 'back_title' => 'VOLUNTEER INFORMATION', 'primary_color' => '#63C7D1', 'secondary_color' => '#DE6900'],
            ['name' => 'Member ID', 'front_title' => 'MEMBER ID', 'back_title' => 'MEMBER INFORMATION', 'primary_color' => '#DE6900', 'secondary_color' => '#1A1A1A'],
            ['name' => 'Staff ID', 'front_title' => 'STAFF ID', 'back_title' => 'STAFF INFORMATION', 'primary_color' => '#1A1A1A', 'secondary_color' => '#63C7D1'],
            ['name' => 'Custom Card', 'front_title' => 'CUSTOM CARD', 'back_title' => 'CARDHOLDER INFORMATION', 'primary_color' => '#DE6900', 'secondary_color' => '#63C7D1'],
        ];

        foreach ($types as $type) {
            CardType::updateOrCreate(['slug' => Str::slug($type['name'])], $type + ['is_active' => true]);
        }
    }
}
