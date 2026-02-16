<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSheetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $sheet = $this->route('sheet');

        return $this->user() && ($this->user()->role === 'admin' || $this->user()->id === $sheet->user_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'subject_id' => 'sometimes|required|exists:subjects,id',
            'type' => 'sometimes|required|in:chapter,midterm,final',
            'chapter_number' => 'nullable|integer|min:1',
            'file' => 'sometimes|required|file|mimes:pdf|max:10240',
        ];
    }
}
