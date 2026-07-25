<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->foreignUlid('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('key');
            $table->string('name');
            $table->text('description')->nullable();

            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique([
                'organisation_id',
                'key',
            ]);

            $table->index([
                'organisation_id',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
