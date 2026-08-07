# Bacapedia - Backend Sistem Manajemen Perpustakaan Digital

Backend RESTful API untuk Sistem Manajemen Perpustakaan **Bacapedia** yang dibangun menggunakan framework **Laravel**, database **MySQL**, autentikasi **Laravel Sanctum**, serta kontrol akses berbasis peran (RBAC).

---

## 🛠️ Persyaratan Sistem

- PHP >= 8.1
- Composer
- MySQL Database Server
- Ext-pdo & Ext-mysqli enabled

---

## 🚀 Cara Instalasi & Konfigurasi

### 1. Inisialisasi Dependensi & Kunci Aplikasi
Jika Anda mendownload repository ini, jalankan perintah berikut untuk menginstal dependensi dan membuat App Key:
```bash
composer install
php artisan key:generate
```

### 2. Konfigurasi Database `.env`
Buka file `.env` dan pastikan konfigurasi MySQL disesuaikan sebagai berikut:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bacapedia_db
DB_USERNAME=root
DB_PASSWORD=
```
> *Pastikan database `bacapedia_db` telah dibuat pada MySQL Server Anda.*

### 3. Jalankan Migration Database
Eksekusi migration untuk membuat skema tabel `users`, `kategori`, `buku`, `peminjam`, dan `personal_access_tokens`:
```bash
php artisan migrate
```

### 4. Jalankan Server Lokal
Jalankan dev server Laravel:
```bash
php artisan serve
```
Secara default API dapat diakses di `http://127.0.0.1:8000`.

---

## 🔑 Header Request Standard
Untuk semua endpoint yang membutuhkan autentikasi (Bearer Token), sertakan header berikut pada request:
- `Content-Type: application/json`
- `Accept: application/json`
- `Authorization: Bearer <token_anda>`

---

## 📚 Daftar Endpoint API

### 1. Autentikasi (`/api`)

| Method | Endpoint | Access | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/register` | Public | Registrasi pengguna baru |
| `POST` | `/api/login` | Public | Login pengguna & dapatkan Bearer Token |
| `POST` | `/api/logout` | Authenticated | Revoke token pengguna yang sedang login |

#### Example Body `POST /api/register`:
```json
{
  "user_id": "USR001",
  "nama": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123",
  "role": "Anggota"
}
```
*Role yang tersedia: `Admin`, `Petugas`, `Anggota` (Default: `Anggota`).*

#### Example Body `POST /api/login`:
```json
{
  "email": "budi@example.com",
  "password": "password123"
}
```

---

### 2. Data Master Kategori (`/api/kategori`)

| Method | Endpoint | Access | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/kategori` | Admin | Menampilkan daftar seluruh kategori |
| `POST` | `/api/kategori` | Admin | Menambahkan kategori baru |
| `GET` | `/api/kategori/{id}` | Admin | Menampilkan detail kategori |
| `PUT` | `/api/kategori/{id}` | Admin | Mengubah data kategori |
| `DELETE` | `/api/kategori/{id}` | Admin | Menghapus data kategori |

#### Example Body `POST /api/kategori`:
```json
{
  "nama_kategori": "Pemrograman & Teknologi"
}
```

---

### 3. Data Master Buku (`/api/buku`)

| Method | Endpoint | Access | Deskripsi |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/buku` | All Roles | Menampilkan daftar seluruh buku |
| `GET` | `/api/buku/{id}` | All Roles | Menampilkan detail buku |
| `POST` | `/api/buku` | Admin | Menambahkan buku baru |
| `PUT` | `/api/buku/{id}` | Admin | Mengubah data buku |
| `DELETE` | `/api/buku/{id}` | Admin | Menghapus data buku |

#### Example Body `POST /api/buku`:
```json
{
  "buku_id": "BUK-001",
  "judul": "Laravel Web Development Premium",
  "penulis": "John Doe",
  "penerbit": "Tech Press",
  "kategori_id": 1,
  "stok": 10,
  "tahun_terbit": 2024
}
```

---

### 4. Transaksi Peminjaman & Pengembalian (`/api`)

| Method | Endpoint | Access | Deskripsi |
| :--- | :--- | :--- | :--- |
| `POST` | `/api/pinjam` | Authenticated | Meminjam buku (Stok > 0, Maksimal 3 buku aktif) |
| `POST` | `/api/kembali/{peminjam_id}` | Authenticated | Mengembalikan buku & kalkulasi denda otomatis |
| `GET` | `/api/riwayat` | Authenticated | Riwayat transaksi (Anggota: Milik sendiri, Admin/Petugas: Semua) |

#### Rules & Logic:
- **Peminjaman (`/api/pinjam`)**:
  - Validasi stok > 0.
  - Validasi pengguna maksimal memiliki **3 peminjaman aktif** (status `'Dipinjam'`).
  - Mengurangi stok buku sebesar 1 menggunakan `DB::transaction`.
  - `tanggal_jatuh_tempo` otomatis diset **7 hari** setelah `tanggal_pinjam`.
- **Pengembalian (`/api/kembali/{peminjam_id}`)**:
  - Mengubah status peminjaman menjadi `'Dikembalikan'`.
  - Mengisi `tanggal_kembali` dengan tanggal hari ini.
  - Mengembalikan stok buku sebesar 1 menggunakan `DB::transaction`.
  - **Denda Otomatis**: Jika `tanggal_kembali > tanggal_jatuh_tempo`, denda dihitung berdasarkan **selisih hari keterlambatan × Rp 2.000**.

#### Example Body `POST /api/pinjam`:
```json
{
  "buku_id": 1
}
```

---

## ⚠️ Response Status Code Standard

| Status Code | Kondisi |
| :--- | :--- |
| **200 OK** | Aksi berhasil dilakukan |
| **201 Created** | Data baru berhasil ditambahkan (Register, Create Book, Pinjam) |
| **401 Unauthenticated** | Token tidak disertakan / token tidak valid |
| **403 Forbidden** | Pengguna tidak memiliki otorisasi peran (RBAC) |
| **404 Not Found** | Data / resource tidak ditemukan di database |
| **409 Conflict** | Stok buku habis atau limit maksimal 3 peminjaman tercapai |
| **422 Unprocessable Entity** | Gagal validasi input (FormRequest error response) |
