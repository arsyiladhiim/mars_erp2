<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_share_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('document_files')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('password_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('allow_download')->default(true);
            $table->boolean('requires_signature')->default(false);
            $table->boolean('is_revoked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('document_share_link_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('share_link_id')->constrained('document_share_links')->cascadeOnDelete();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('action', ['view', 'download', 'sign'])->default('view');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_share_link_accesses');
        Schema::dropIfExists('document_share_links');
    }
};
