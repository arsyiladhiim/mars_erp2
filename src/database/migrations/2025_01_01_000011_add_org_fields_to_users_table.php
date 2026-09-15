<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->foreignId('company_id')->nullable()->after('employee_code')
                ->constrained('core_companies')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('company_id')
                ->constrained('core_branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('branch_id')
                ->constrained('core_departments')->nullOnDelete();
            $table->string('phone')->nullable()->after('email');
            $table->string('job_title')->nullable()->after('phone');
            $table->string('avatar_path')->nullable()->after('job_title');
            $table->boolean('is_active')->default(true)->after('avatar_path');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn([
                'employee_code', 'phone', 'job_title', 'avatar_path',
                'is_active', 'last_login_at', 'last_login_ip', 'deleted_at',
            ]);
        });
    }
};
