<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('document_type')->nullable(); // contract, invoice, quotation, signed_document...
            $table->nullableMorphs('attachable'); // any business object
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->foreignId('parent_document_id')->nullable()->constrained('document_files')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->date('retention_until')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_files');
    }
};
