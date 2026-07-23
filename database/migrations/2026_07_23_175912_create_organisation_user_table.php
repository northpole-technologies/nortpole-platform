<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('role')->default('member');
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();

            $table->timestamps();

            $table->unique([
                'organisation_id',
                'user_id',
            ]);

            $table->index([
                'user_id',
                'is_active',
            ]);

            $table->index([
                'organisation_id',
                'role',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_user');
    }
};