<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KategoriRequest;
use App\Models\Kategori;
use Illuminate\Http\JsonResponse;

class KategoriController extends Controller
{
    public function index(): JsonResponse
    {
        $kategori = Kategori::all();
        return response()->json([
            'message' => 'Daftar kategori berhasil diambil',
            'data' => $kategori
        ], 200);
    }

    public function store(KategoriRequest $request): JsonResponse
    {
        $kategori = Kategori::create($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $kategori = Kategori::findOrFail($id);

        return response()->json([
            'message' => 'Detail kategori berhasil diambil',
            'data' => $kategori
        ], 200);
    }

    public function update(KategoriRequest $request, $id): JsonResponse
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->update($request->validated());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui',
            'data' => $kategori
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $kategori = Kategori::findOrFail($id);
        $kategori->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ], 200);
    }
}
