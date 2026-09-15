<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('document_files')->cascadeOnDelete();
            $table->foreignId('share_link_id')->nullable()->constrained('document_share_links')->nullOnDelete();
            $table->string('signer_name');
            $table->string('signer_email')->nullable();
            $table->string('document_hash');
            $table->string('signature_image_path')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['pending', 'signed', 'declined', 'expired'])->default('pending');
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
    }
};
