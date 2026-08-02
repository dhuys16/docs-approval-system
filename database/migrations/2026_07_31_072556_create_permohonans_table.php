<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permohonans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_permohonan')->unique();
            $table->foreignId('pemohon_id')->constrained('users')->onDelete('cascade');
            $table->string('judul_project');
            $table->text('deskripsi')->nullable();
            $table->enum('status', ['draft', 'submitted', 'revisi', 'approved', 'rejected'])->default('draft');
            $table->timestamps();
            $table->index('status');
            $table->index('pemohon_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permohonans');
    }
};