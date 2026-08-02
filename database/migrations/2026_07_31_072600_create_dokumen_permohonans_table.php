<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_permohonans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permohonan_id')->constrained('permohonans')->onDelete('cascade');
            $table->string('nama_file');
            $table->string('file_path');
            $table->integer('file_size');
            $table->timestamps();
            $table->index('permohonan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_permohonans');
    }
};