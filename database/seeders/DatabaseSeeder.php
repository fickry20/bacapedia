<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@bacapedia.id'],
            [
                'user_id' => 'ADM-001',
                'nama' => 'Administrator Bacapedia',
                'password' => Hash::make('password123'),
                'role' => 'Admin',
            ]
        );

        $anggota = User::firstOrCreate(
            ['email' => 'anggota@bacapedia.id'],
            [
                'user_id' => 'ANG-001',
                'nama' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'Anggota',
            ]
        );

        // 2. Seed Kategori
        $kategoriProg = Kategori::firstOrCreate(['nama_kategori' => 'Pemrograman']);
        $kategoriFiksi = Kategori::firstOrCreate(['nama_kategori' => 'Fiksi']);
        $kategoriSains = Kategori::firstOrCreate(['nama_kategori' => 'Sains & Teknologi']);

        // 3. Seed Buku
        Buku::firstOrCreate(
            ['buku_id' => 'BK-001'],
            [
                'judul' => 'Belajar Backend Laravel 10',
                'penulis' => 'Taylor Otwell',
                'penerbit' => 'Bacapedia Press',
                'kategori_id' => $kategoriProg->id,
                'stok' => 5,
                'tahun_terbit' => 2023,
            ]
        );

        Buku::firstOrCreate(
            ['buku_id' => 'BK-002'],
            [
                'judul' => 'Laskar Pelangi',
                'penulis' => 'Andrea Hirata',
                'penerbit' => 'Bentang Pustaka',
                'kategori_id' => $kategoriFiksi->id,
                'stok' => 3,
                'tahun_terbit' => 2005,
            ]
        );

        Buku::firstOrCreate(
            ['buku_id' => 'BK-003'],
            [
                'judul' => 'Pengantar Kecerdasan Buatan',
                'penulis' => 'Prof. Dr. Alan Turing',
                'penerbit' => 'Informatika',
                'kategori_id' => $kategoriSains->id,
                'stok' => 4,
                'tahun_terbit' => 2022,
            ]
        );
    }
}

