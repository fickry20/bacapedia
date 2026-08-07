<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BukuRequest;
use App\Models\Buku;
use Illuminate\Http\JsonResponse;

class BukuController extends Controller
{
    public function index(): JsonResponse
    {
        $buku = Buku::with('kategori')->get();
        return response()->json([
            'message' => 'Daftar buku berhasil diambil',
            'data' => $buku
        ], 200);
    }

    public function store(BukuRequest $request): JsonResponse
    {
        $buku = Buku::create($request->validated());

        return response()->json([
            'message' => 'Buku berhasil ditambahkan',
            'data' => $buku->load('kategori')
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $buku = Buku::with('kategori')->findOrFail($id);

        return response()->json([
            'message' => 'Detail buku berhasil diambil',
            'data' => $buku
        ], 200);
    }

    public function update(BukuRequest $request, $id): JsonResponse
    {
        $buku = Buku::findOrFail($id);
        $buku->update($request->validated());

        return response()->json([
            'message' => 'Buku berhasil diperbarui',
            'data' => $buku->load('kategori')
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return response()->json([
            'message' => 'Buku berhasil dihapus'
        ], 200);
    }
}
