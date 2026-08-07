<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PinjamRequest;
use App\Models\Buku;
use App\Models\Peminjam;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function pinjam(PinjamRequest $request): JsonResponse
    {
        $user = $request->user();
        $buku = Buku::findOrFail($request->buku_id);

        if ($buku->stok <= 0) {
            return response()->json([
                'message' => 'Stok buku habis'
            ], 409);
        }

        $activeBorrowings = Peminjam::where('user_id', $user->id)
            ->where('status', 'Dipinjam')
            ->count();

        if ($activeBorrowings >= 3) {
            return response()->json([
                'message' => 'Batas maksimal peminjaman aktif (3 buku) telah tercapai'
            ], 409);
        }

        $peminjam = DB::transaction(function () use ($user, $buku) {
            $record = Peminjam::create([
                'user_id' => $user->id,
                'buku_id' => $buku->id,
                'tanggal_pinjam' => Carbon::now()->toDateString(),
                'tanggal_jatuh_tempo' => Carbon::now()->addDays(7)->toDateString(),
                'tanggal_kembali' => null,
                'status' => 'Dipinjam',
                'denda' => 0,
            ]);

            $buku->decrement('stok');

            return $record;
        });

        return response()->json([
            'message' => 'Peminjaman buku berhasil',
            'data' => $peminjam->load(['user', 'buku'])
        ], 201);
    }

    public function kembali($peminjam_id): JsonResponse
    {
        $peminjam = Peminjam::with(['user', 'buku'])->find($peminjam_id);

        if (!$peminjam) {
            return response()->json([
                'message' => 'Data peminjaman tidak ditemukan'
            ], 404);
        }

        if ($peminjam->status === 'Dikembalikan') {
            return response()->json([
                'message' => 'Buku ini sudah dikembalikan sebelumnya'
            ], 422);
        }

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($peminjam->tanggal_jatuh_tempo)->startOfDay();

        $denda = 0;
        if ($today->greaterThan($dueDate)) {
            $overdueDays = $dueDate->diffInDays($today);
            $denda = (int) ($overdueDays * 2000);
        }

        DB::transaction(function () use ($peminjam, $today, $denda) {
            $peminjam->update([
                'tanggal_kembali' => $today->toDateString(),
                'status' => 'Dikembalikan',
                'denda' => $denda,
            ]);

            $peminjam->buku->increment('stok');
        });

        return response()->json([
            'message' => 'Pengembalian buku berhasil',
            'data' => $peminjam->fresh(['user', 'buku'])
        ], 200);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'Anggota') {
            $riwayat = Peminjam::where('user_id', $user->id)
                ->with(['user', 'buku.kategori'])
                ->latest()
                ->get();
        } else {
            $riwayat = Peminjam::with(['user', 'buku.kategori'])
                ->latest()
                ->get();
        }

        return response()->json([
            'message' => 'Riwayat peminjaman berhasil diambil',
            'data' => $riwayat
        ], 200);
    }
}
