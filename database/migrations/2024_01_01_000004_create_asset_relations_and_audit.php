<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // asset_assignments
        // ─────────────────────────────────────────────
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->timestamp('assigned_from');
            $table->timestamp('assigned_to')->nullable();

            $table->string('assigned_by_employee_id', 5);  // FK Employee (AK)
            $table->string('assignment_type');              // User / CostCenter

            $table->string('employee_id', 5)->nullable();
            $table->uuid('cost_center_id')->nullable();
            $table->foreign('cost_center_id')->references('id')->on('cost_centers')->nullOnDelete();

            // Snapshots
            $table->string('employee_display_name_snapshot')->nullable();
            $table->string('employee_mail_snapshot')->nullable();
            $table->string('cost_center_code_snapshot')->nullable();
            $table->string('cost_center_name_snapshot')->nullable();

            $table->text('comment')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['asset_id', 'assigned_to']);   // aktive Zuweisung: assigned_to IS NULL
        });

        // ─────────────────────────────────────────────
        // asset_transactions  (Issue / Return)
        // ─────────────────────────────────────────────
        Schema::create('asset_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();

            $table->string('transaction_type');            // Issue / Return
            $table->timestamp('transaction_date');

            $table->string('performed_by_employee_id', 5);
            $table->string('counterparty_employee_id', 5)->nullable();

            // Snapshots
            $table->string('counterparty_display_name_snapshot')->nullable();
            $table->string('counterparty_mail_snapshot')->nullable();
            $table->string('counterparty_cost_center_code_snapshot')->nullable();

            $table->uuid('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('locations')->nullOnDelete();

            $table->string('signature_document_storage_key')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['asset_id', 'transaction_type']);
        });

        // ─────────────────────────────────────────────
        // status_histories  (polymorph über entity_name + entity_id)
        // ─────────────────────────────────────────────
        Schema::create('status_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('entity_name');    // Asset / Contract / Machine
            $table->uuid('entity_id');

            $table->uuid('status_definition_id');
            // $table->foreign('status_definition_id')
            //       ->references('id')->on('status_definitions')->noActionOnDelete();

            $table->timestamp('valid_from');
            $table->timestamp('valid_to')->nullable();
            $table->timestamp('changed_at');
            $table->string('changed_by_employee_id', 5);
            $table->text('comment')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['entity_name', 'entity_id']);
        });

        Schema::table('status_histories', function (Blueprint $table) {
            $table->foreign('status_definition_id')
                  ->references('id')->on('status_definitions')->noActionOnDelete();
        });

        // ─────────────────────────────────────────────
        // attachments
        // ─────────────────────────────────────────────
        Schema::create('attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            // Polymorph: entity_name + entity_id statt morphs (konsistent mit Audit)
            $table->string('entity_name');
            $table->uuid('entity_id');

            $table->string('storage_key')->nullable();
            $table->uuid('dms_guid')->nullable();
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->timestamp('uploaded_at');
            $table->string('uploaded_by_employee_id', 5);

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['entity_name', 'entity_id']);
        });

        // ─────────────────────────────────────────────
        // audit_logs  +  audit_log_details
        // ─────────────────────────────────────────────
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->string('entity_name');
            $table->uuid('entity_id');
            $table->string('action');              // Create/Update/Delete/Archive/Issue/Return
            $table->timestamp('changed_at');
            $table->string('changed_by_employee_id', 5);
            $table->string('source')->nullable();  // UI / API / Import / Scheduler
            $table->string('correlation_id')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index(['entity_name', 'entity_id']);
            $table->index('changed_at');
        });

        Schema::create('audit_log_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('business_code');

            $table->uuid('audit_log_id');
            $table->foreign('audit_log_id')->references('id')->on('audit_logs')->cascadeOnDelete();

            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('data_type')->nullable();

            $table->timestamps();
            $table->string('created_by_employee_id', 5);
            $table->string('updated_by_employee_id', 5)->nullable();
            $table->softDeletes();
            $table->string('deleted_by_employee_id', 5)->nullable();

            $table->index('audit_log_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log_details');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('status_histories');
        Schema::dropIfExists('asset_transactions');
        Schema::dropIfExists('asset_assignments');
    }
};
