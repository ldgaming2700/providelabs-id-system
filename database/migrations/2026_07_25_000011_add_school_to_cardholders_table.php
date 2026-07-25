<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cardholders', function (Blueprint $table) {
            $table->string('school')->nullable()->after('position');
        });
    }

    public function down(): void
    {
        Schema::table('cardholders', function (Blueprint $table) {
            $table->dropColumn('school');
        });
    }
};