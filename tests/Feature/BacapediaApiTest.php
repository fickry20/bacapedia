<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjam;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BacapediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_and_login()
    {
        $response = $this->postJson('/api/register', [
            'user_id' => 'USR001',
            'nama' => 'Admin User',
            'email' => 'admin@bacapedia.id',
            'password' => 'password123',
            'role' => 'Admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data', 'access_token']);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'admin@bacapedia.id',
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['message', 'data', 'access_token']);
    }

    public function test_kategori_crud_authorization()
    {
        $admin = User::factory()->create(['user_id' => 'ADM01', 'role' => 'Admin']);
        $anggota = User::factory()->create(['user_id' => 'ANG01', 'role' => 'Anggota']);

        // Anggota cannot access Kategori CRUD (HTTP 403)
        $this->actingAs($anggota, 'sanctum')
            ->getJson('/api/kategori')
            ->assertStatus(403);

        // Admin can create Kategori
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/kategori', ['nama_kategori' => 'Teknologi']);

        $response->assertStatus(201);
        $this->assertDatabaseHas('kategori', ['nama_kategori' => 'Teknologi']);
    }

    public function test_buku_crud_and_access_control()
    {
        $admin = User::factory()->create(['user_id' => 'ADM01', 'role' => 'Admin']);
        $anggota = User::factory()->create(['user_id' => 'ANG01', 'role' => 'Anggota']);

        $kategori = Kategori::create(['nama_kategori' => 'Sains']);

        // Admin creates Buku
        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/buku', [
                'buku_id' => 'BUK001',
                'judul' => 'Fisika Kuantum',
                'penulis' => 'Albert Einstein',
                'penerbit' => 'Science Press',
                'kategori_id' => $kategori->id,
                'stok' => 5,
                'tahun_terbit' => 2022,
            ]);

        $response->assertStatus(201);

        // Anggota can read Buku list
        $this->actingAs($anggota, 'sanctum')
            ->getJson('/api/buku')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');

        // Anggota cannot delete Buku (HTTP 403)
        $this->actingAs($anggota, 'sanctum')
            ->deleteJson('/api/buku/' . $response->json('data.id'))
            ->assertStatus(403);
    }

    public function test_pinjam_and_kembali_with_fine_calculation()
    {
        $anggota = User::factory()->create(['user_id' => 'ANG01', 'role' => 'Anggota']);
        $kategori = Kategori::create(['nama_kategori' => 'Komputer']);
        $buku = Buku::create([
            'buku_id' => 'BUK002',
            'judul' => 'Pemrograman PHP',
            'penulis' => 'Rasmus Lerdorf',
            'penerbit' => 'O\'Reilly',
            'kategori_id' => $kategori->id,
            'stok' => 2,
            'tahun_terbit' => 2023,
        ]);

        // Pinjam buku
        $pinjamResponse = $this->actingAs($anggota, 'sanctum')
            ->postJson('/api/pinjam', ['buku_id' => $buku->id]);

        $pinjamResponse->assertStatus(201);
        $this->assertEquals(1, $buku->fresh()->stok);

        $peminjamId = $pinjamResponse->json('data.id');

        // Simulate overdue borrowing (10 days ago, due 3 days ago -> 3 days overdue -> fine = 6000)
        $peminjam = Peminjam::find($peminjamId);
        $peminjam->update([
            'tanggal_pinjam' => Carbon::now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => Carbon::now()->subDays(3)->toDateString(),
        ]);

        // Pengembalian
        $kembaliResponse = $this->actingAs($anggota, 'sanctum')
            ->postJson('/api/kembali/' . $peminjamId);

        $kembaliResponse->assertStatus(200);
        $this->assertEquals('Dikembalikan', $kembaliResponse->json('data.status'));
        $this->assertEquals(6000, $kembaliResponse->json('data.denda'));
        $this->assertEquals(2, $buku->fresh()->stok);
    }

    public function test_borrow_limit_exceeded()
    {
        $anggota = User::factory()->create(['user_id' => 'ANG02', 'role' => 'Anggota']);
        $kategori = Kategori::create(['nama_kategori' => 'Novel']);
        $buku = Buku::create([
            'buku_id' => 'BUK003',
            'judul' => 'Laskar Pelangi',
            'penulis' => 'Andrea Hirata',
            'penerbit' => 'Bentang',
            'kategori_id' => $kategori->id,
            'stok' => 10,
            'tahun_terbit' => 2005,
        ]);

        // Create 3 active borrowings
        for ($i = 0; $i < 3; $i++) {
            Peminjam::create([
                'user_id' => $anggota->id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => Carbon::now()->toDateString(),
                'tanggal_jatuh_tempo' => Carbon::now()->addDays(7)->toDateString(),
                'status' => 'Dipinjam',
                'denda' => 0,
            ]);
        }

        // 4th borrowing attempt should fail with HTTP 409
        $response = $this->actingAs($anggota, 'sanctum')
            ->postJson('/api/pinjam', ['buku_id' => $buku->id]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'Batas maksimal peminjaman aktif (3 buku) telah tercapai']);
    }
}
