<?php

namespace Modules\Books\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bookId = $this->route('book')->id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'isbn' => ['sometimes', 'string', 'regex:/^(?=(?:\D*\d){10}(?:(?:\D*\d){3})?$)[\d-]+$/i', Rule::unique('books', 'isbn')->ignore($bookId)],
            'publication_year' => ['sometimes', 'integer', 'min:1900', 'max:' . date('Y')],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'author_id' => ['sometimes', 'exists:authors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'isbn.regex' => 'Format ISBN tidak valid.',
            'isbn.unique' => 'ISBN sudah digunakan.',
            'publication_year.integer' => 'Tahun terbit harus berupa bilangan bulat.',
            'publication_year.min' => 'Tahun terbit minimal 1900.',
            'publication_year.max' => 'Tahun terbit maksimal tahun sekarang (' . date('Y') . ').',
            'price.numeric' => 'Harga harus berupa angka.',
            'price.min' => 'Harga tidak boleh negatif.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'author_id.exists' => 'Penulis yang dipilih tidak valid.',
        ];
    }
}
