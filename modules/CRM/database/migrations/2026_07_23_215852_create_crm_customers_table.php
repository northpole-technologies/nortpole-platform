<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customers', function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->foreignUlid('organisation_id')
                ->constrained('organisations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('type', 30)->default('individual');
            $table->string('status', 30)->default('active');

            $table->string('name');
            $table->string('company_name')->nullable();

            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->string('website')->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();
            $table->string('postal_code', 30)->nullable();
            $table->string('country', 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'status']);
            $table->index(['organisation_id', 'type']);
            $table->index(['organisation_id', 'name']);
            $table->index(['organisation_id', 'company_name']);

            $table->unique(
                ['organisation_id', 'email'],
                'crm_customers_organisation_email_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customers');
    }
};