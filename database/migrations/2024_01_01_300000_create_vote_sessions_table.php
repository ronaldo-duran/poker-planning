<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vote_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('story_title')->nullable();
            $table->text('story_description')->nullable();
            $table->enum('status', ['open', 'revealing', 'revealed', 'closed'])->default('open');
            $table->decimal('average', 8, 2)->nullable();
            $table->timestamp('revealed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vote_sessions');
    }
};
