<?php

namespace Modules\Books\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', 'regex:/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/i', 'unique:books,isbn'],
            'publication_year' => ['required', 'integer', 'min:1900', 'max:' . date('Y')],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'author_id' => ['required', 'exists:authors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.required' => 'ISBN wajib diisi.',
            'isbn.regex' => 'Format ISBN tidak valid.',
            'isbn.unique' => 'ISBN sudah digunakan.',
            'publication_year.required' => 'Tahun terbit wajib diisi.',
            'publication_year.integer' => 'Tahun terbit harus berupa bilangan bulat.',
            'publication_year.min' => 'Tahun terbit minimal 1900.',
            'publication_year.max' => 'Tahun terbit maksimal tahun sekarang (' . date('Y') . ').',
            'price.required' => 'Harga wajib diisi.',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'author_id.required' => 'Penulis wajib dipilih.',
            'author_id.exists' => 'Penulis yang dipilih tidak valid.',
        ];
    }
}
