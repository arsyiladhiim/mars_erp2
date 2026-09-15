<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('helpdesk_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->foreignId('requester_id')->constrained('users');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('core_departments')->nullOnDelete();
            $table->string('category')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedInteger('sla_hours')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->nullableMorphs('related_asset');
            $table->foreignId('related_customer_id')->nullable()->constrained('master_business_partners')->nullOnDelete();
            $table->enum('status', ['open', 'assigned', 'in_progress', 'pending', 'resolved', 'closed'])
                ->default('open');
            $table->timestamps();
        });

        Schema::create('helpdesk_ticket_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('helpdesk_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('helpdesk_ticket_comments');
        Schema::dropIfExists('helpdesk_tickets');
    }
};
