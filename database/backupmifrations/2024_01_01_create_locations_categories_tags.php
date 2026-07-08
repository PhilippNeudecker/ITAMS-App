<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // locations  (selbstreferenzierend)
        // ─────────────────────────────────────────────
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('name');
            $table->text('description')->nullable();

            $table->uuid('parent_location_id')->nullable();
            $table->foreign('parent_location_id')->references('id')->on('locations')->nullOnDelete();

            $table->uuid('location_type_definition_id');
            $table->foreign('location_type_definition_id')
                  ->references('id')->on('location_type_definitions')->restrictOnDelete();

            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->text('additional_info')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['business_code', 'parent_location_id']);
        });

        // ─────────────────────────────────────────────
        // manufacturers
        // ─────────────────────────────────────────────
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('name');
            $table->string('website')->nullable();
            $table->string('support_contact')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('business_code');
        });

        // ─────────────────────────────────────────────
        // tags
        // ─────────────────────────────────────────────
        Schema::create('tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();  // #RRGGBB
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('business_code');
        });

        // ─────────────────────────────────────────────
        // categories  (selbstreferenzierend, mit Label-Rules)
        // ─────────────────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();

            $table->uuid('parent_category_id')->nullable();
            $table->foreign('parent_category_id')->references('id')->on('categories')->nullOnDelete();

            // Label-Rules
            $table->string('asset_prefix', 9);
            $table->string('asset_separator', 5)->default('-');
            $table->unsignedTinyInteger('asset_number_length')->default(6);

            // Warranty Defaults
            $table->unsignedSmallInteger('default_warranty_days')->nullable();
            $table->unsignedSmallInteger('default_warranty_notify_days_before')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['business_code', 'parent_category_id']);
        });

        // ─────────────────────────────────────────────
        // category_sequences  (Zähler pro Kategorie)
        // ─────────────────────────────────────────────
        Schema::create('category_sequences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('category_id')->unique();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();

            $table->unsignedInteger('next_sequence_number')->default(1);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_sequences');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('locations');
    }
};
