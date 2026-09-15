<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->nullableMorphs('maintainable'); // fixed asset or IT asset
            $table->enum('type', ['preventive', 'corrective'])->default('corrective');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('cost', 18, 2)->default(0);
            $table->text('parts_used')->nullable();
            $table->enum('status', ['requested', 'scheduled', 'in_progress', 'completed', 'cancelled'])
                ->default('requested');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
