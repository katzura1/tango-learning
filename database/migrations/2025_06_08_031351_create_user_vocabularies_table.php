<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('vocabulary_id')->references('id')->on('vocabularies')->onDelete('cascade');
            $table->string('status')->default('learning');          // e.g., learning, mastered
            $table->enum('favorite', ['yes', 'no'])->default('no'); // Indicates if the vocabulary is a favorite
            $table->dateTime('last_reviewed_at')->nullable();       // Last time the user reviewed this vocabulary
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vocabularies');
    }
};
