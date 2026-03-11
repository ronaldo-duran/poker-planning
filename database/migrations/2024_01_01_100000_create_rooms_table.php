<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 8)->unique();
            $table->string('logo')->nullable();
            $table->foreignId('host_id')->constrained('users')->cascadeOnDelete();
            $table->json('card_config')->default(json_encode([0, 1, 2, 3, 5, 8, 13, 21, '?']));
            $table->enum('state', ['waiting', 'voting', 'discussion', 'break'])->default('waiting');
            $table->boolean('emojis_blocked')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
