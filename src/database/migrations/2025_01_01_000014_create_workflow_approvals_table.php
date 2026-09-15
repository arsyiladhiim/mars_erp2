<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_rule_id')->nullable()->constrained('workflow_rules')->nullOnDelete();
            $table->morphs('approvable'); // approvable_type, approvable_id -> any document
            $table->unsignedInteger('sequence')->default(1);
            $table->string('step_name')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('delegated_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'returned', 'escalated', 'cancelled'])
                ->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_approvals');
    }
};
