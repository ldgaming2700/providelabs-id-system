<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('card_types')->updateOrInsert(
            [
                'slug' => 'sangguniang-kabataan',
            ],
            [
                'name' => 'Sangguniang Kabataan',
                'front_title' => 'MAKABAGONG KABATAAN NG SAN ISIDRO',
                'back_title' => 'MAKABAGONG KABATAAN NG SAN ISIDRO',
                'primary_color' => '#D51D10',
                'secondary_color' => '#2229C8',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('card_types')
            ->where('slug', 'sangguniang-kabataan')
            ->delete();
    }
};