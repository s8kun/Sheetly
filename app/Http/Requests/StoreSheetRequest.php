<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'type' => 'required|in:chapter,midterm,final',
            'chapter_number' => 'required_if:type,chapter|nullable|integer|min:1',
            'file' => 'required|file|mimes:pdf|max:20480',        ];
    }
}
