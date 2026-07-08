<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // cost_centers
        // ─────────────────────────────────────────────
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('cost_center_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('business_code');
        });

        // ─────────────────────────────────────────────
        // employees  (AD-sync, kein Auth-Login direkt)
        // ─────────────────────────────────────────────
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('employee_id', 5)->unique();   // Alternate Key (AD)
            $table->string('external_object_id')->nullable(); // Azure ObjectId
            $table->string('upn')->nullable();
            $table->string('mail')->nullable();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('display_name')->nullable();

            $table->uuid('cost_center_id')->nullable();
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();

            $table->string('ad_status');               // active / disabled / deleted
            $table->timestamp('last_sync_at');

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('business_code');
            $table->index('employee_id');
        });

        // ─────────────────────────────────────────────
        // status_definitions
        // ─────────────────────────────────────────────
        Schema::create('status_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('module');    // Asset / Contract / Machine / ...
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['business_code', 'module']);
        });

        // ─────────────────────────────────────────────
        // location_type_definitions
        // ─────────────────────────────────────────────
        Schema::create('location_type_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('business_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_type_definitions');
        Schema::dropIfExists('status_definitions');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('cost_centers');
    }
};
