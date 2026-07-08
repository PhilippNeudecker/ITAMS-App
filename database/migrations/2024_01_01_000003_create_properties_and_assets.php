<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // property_definitions
        // ─────────────────────────────────────────────
        Schema::create('property_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('scope');       // Asset / Material / ...
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('data_type');   // Text / Number / Date / Boolean / Option
            $table->string('unit')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['business_code', 'scope']);
        });

        // ─────────────────────────────────────────────
        // property_options
        // ─────────────────────────────────────────────
        Schema::create('property_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('property_definition_id');
            $table->foreign('property_definition_id')
                  ->references('id')->on('property_definitions')->cascadeOnDelete();

            $table->string('option_value');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('property_definition_id');
        });

        // ─────────────────────────────────────────────
        // category_property_definitions  (Pivot + Defaults)
        // ─────────────────────────────────────────────
        Schema::create('category_property_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();

            $table->uuid('property_definition_id');
            $table->foreign('property_definition_id')
                  ->references('id')->on('property_definitions')->cascadeOnDelete();

            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);

            // Typisierte Defaults
            $table->text('default_value_text')->nullable();
            $table->decimal('default_value_number', 15, 4)->nullable();
            $table->date('default_value_date')->nullable();
            $table->boolean('default_value_bool')->nullable();
            $table->uuid('default_property_option_id')->nullable();
            // $table->foreign('default_property_option_id')
            //       ->references('id')->on('property_options')->nullOnDelete();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->unique(['category_id', 'property_definition_id']);
        });

        Schema::table('category_property_definitions', function (Blueprint $table) {
            $table->foreign('default_property_option_id')
                  ->references('id')->on('property_options')->noActionOnDelete();
        });

        // ─────────────────────────────────────────────
        // assets
        // ─────────────────────────────────────────────
        Schema::create('assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('asset_label')->unique();           // LAP-000001
            $table->integer('asset_sequence_number');

            $table->uuid('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->noActionOnDelete();

            $table->uuid('status_definition_id');
            $table->foreign('status_definition_id')
                  ->references('id')->on('status_definitions')->noActionOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->uuid('manufacturer_id')->nullable();
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->nullOnDelete();

            // Supplier Snapshot (kein eigenes Modell)
            $table->string('supplier_number')->nullable();
            $table->string('supplier_name')->nullable();
            $table->text('supplier_address')->nullable();
            $table->string('supplier_post_code')->nullable();
            $table->string('supplier_city')->nullable();
            $table->string('supplier_country')->nullable();

            $table->uuid('current_location_id')->nullable();
            $table->foreign('current_location_id')->references('id')->on('locations')->nullOnDelete();

            $table->string('image_storage_key')->nullable();
            $table->string('barcode_value')->nullable();
            $table->string('qr_value')->nullable();

            $table->decimal('purchase_value', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->uuid('purchase_document_guid')->nullable();

            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->unsignedSmallInteger('warranty_notify_days_before')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['business_code', 'category_id']);
            $table->index('status_definition_id');
            $table->index('current_location_id');
        });

        // ─────────────────────────────────────────────
        // asset_tag  (Pivot)
        // ─────────────────────────────────────────────
        Schema::create('asset_tag', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->uuid('tag_id');
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->unique(['asset_id', 'tag_id']);
        });

        // ─────────────────────────────────────────────
        // asset_property_values
        // ─────────────────────────────────────────────
        Schema::create('asset_property_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->uuid('property_definition_id');
            $table->foreign('property_definition_id')
                  ->references('id')->on('property_definitions')->noActionOnDelete();

            $table->text('value_text')->nullable();
            $table->decimal('value_number', 15, 4)->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('value_bool')->nullable();

            $table->uuid('property_option_id')->nullable();
            $table->foreign('property_option_id')->references('id')->on('property_options')->nullOnDelete();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->unique(['asset_id', 'property_definition_id']);
            $table->index('asset_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_property_values');
        Schema::dropIfExists('asset_tag');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('category_property_definitions');
        Schema::dropIfExists('property_options');
        Schema::dropIfExists('property_definitions');
    }
};
