<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_rule_id')->constrained('workflow_rules')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('name'); // Manager, Finance, Director
            $table->enum('approver_type', ['role', 'user', 'department_head'])->default('role');
            $table->string('approver_role')->nullable();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('mode', ['sequential', 'parallel'])->default('sequential');
            $table->unsignedInteger('deadline_hours')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
