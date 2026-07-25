<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();

            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('module_key')->default('core');
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index([
                'module_key',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
