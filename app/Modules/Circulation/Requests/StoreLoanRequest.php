<?php

namespace Modules\Circulation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Buang baris form yang kosong (buku belum dipilih)
        $items = collect($this->input('items', []))
            ->filter(fn ($item) => ! empty($item['book_id']))
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }

    public function rules(): array
    {
        return [
            'member_id' => ['required', 'integer'],
            'days' => ['nullable', 'integer', 'between:1,30'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.book_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Pilih anggota terlebih dahulu.',
            'items.required' => 'Pilih minimal 1 buku.',
            'items.min' => 'Pilih minimal 1 buku.',
            'items.*.quantity.max' => 'Maksimal 5 eksemplar per judul.',
        ];
    }
}
