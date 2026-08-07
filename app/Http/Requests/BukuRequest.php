<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BukuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bukuId = $this->route('buku');
        if (is_object($bukuId)) {
            $bukuId = $bukuId->id;
        }

        return [
            'buku_id' => [
                'required',
                'string',
                Rule::unique('buku', 'buku_id')->ignore($bukuId),
            ],
            'judul' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori,id',
            'stok' => 'required|integer|min:0',
            'tahun_terbit' => 'required|integer',
        ];
    }
}
