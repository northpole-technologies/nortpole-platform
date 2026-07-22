<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisation_modules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('organisation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('marketplace_module_id')
                ->constrained('marketplace_modules')
                ->cascadeOnDelete();

            $table->boolean('is_enabled')->default(true);
            $table->timestamp('installed_at')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'organisation_id',
                'marketplace_module_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_modules');
    }
};