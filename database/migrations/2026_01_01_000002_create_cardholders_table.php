<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cardholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('id_no')->unique();
            $table->string('name');
            $table->string('sc_id')->nullable();
            $table->string('philhealth')->nullable();
            $table->string('cellphone_no')->nullable();
            $table->text('address')->nullable();
            $table->string('position')->nullable();
            $table->date('birthday')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();
            $table->string('relationship')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('photo_status')->default('placeholder');
            $table->string('status')->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'card_type_id']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cardholders');
    }
};
