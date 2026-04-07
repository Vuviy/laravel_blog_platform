<?php

namespace Modules\Article\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
//            'title' => 'required|min:3|max:100',
//            'text' => 'required|min:10',
            'status' => 'boolean',
            'tags' => 'array',
            'translations' => 'array',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->boolean('status'),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
