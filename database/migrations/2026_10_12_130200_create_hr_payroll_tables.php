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
        Schema::create('hr_employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_number');
            $table->string('full_name');
            $table->string('ic_passport_number');
            $table->uuid('department_id')->nullable();
            $table->string('position');
            $table->date('join_date');
            $table->decimal('basic_salary', 14, 2);
            $table->decimal('epf_rate', 5, 2)->default(11.00);
            $table->string('status')->default('active'); // active, resigned, terminated
            $table->decimal('annual_leave_balance', 5, 1)->default(14.0);
            $table->decimal('medical_leave_balance', 5, 1)->default(14.0);
            $table->timestamps();

            $table->unique(['org_id', 'employee_number']);
        });

        Schema::create('hr_attendance_days', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status')->default('present'); // present, late, absent, leave
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('selfie_image')->nullable();
            $table->timestamps();

            $table->unique(['org_id', 'employee_id', 'date']);
        });

        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('leave_type'); // annual, medical, unpaid, maternity, paternity
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 4, 1);
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignUuid('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->string('claim_type');
            $table->decimal('amount', 14, 2);
            $table->date('claim_date');
            $table->string('receipt_file_url')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, paid
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_payroll_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->string('status')->default('draft'); // draft, approved, paid
            $table->decimal('total_gross', 14, 2)->default(0.00);
            $table->decimal('total_statutory', 14, 2)->default(0.00);
            $table->decimal('total_net', 14, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['org_id', 'period_month', 'period_year']);
        });

        Schema::create('hr_payslips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('orgs')->cascadeOnDelete();
            $table->foreignUuid('payroll_run_id')->constrained('hr_payroll_runs')->cascadeOnDelete();
            $table->foreignUuid('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->decimal('gross_pay', 14, 2);
            $table->decimal('epf_employee', 14, 2);
            $table->decimal('epf_employer', 14, 2);
            $table->decimal('socso_employee', 14, 2);
            $table->decimal('socso_employer', 14, 2);
            $table->decimal('eis_employee', 14, 2);
            $table->decimal('eis_employer', 14, 2);
            $table->decimal('pcb_amount', 14, 2)->default(0.00);
            $table->decimal('net_pay', 14, 2);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_payslips');
        Schema::dropIfExists('hr_payroll_runs');
        Schema::dropIfExists('hr_claims');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_attendance_days');
        Schema::dropIfExists('hr_employees');
    }
};
