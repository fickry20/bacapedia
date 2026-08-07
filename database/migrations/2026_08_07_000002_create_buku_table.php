<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('buku_id')->unique();
            $table->string('judul');
            $table->string('penulis');
            $table->string('penerbit');
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
            $table->integer('stok');
            $table->integer('tahun_terbit');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
