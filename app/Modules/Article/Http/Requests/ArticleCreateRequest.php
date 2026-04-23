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
            'status' => 'boolean',
            'slug' => 'required|string|max:255',
            'tags' => 'array',
            'tags.*' => 'uuid',
            'translations' => 'array',
            'translations.*' => 'array',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.text' => 'required|string',
            'translations.*.seo_title' => 'nullable|string|max:60',
            'translations.*.seo_description' => 'nullable|string|max:160',
            'translations.*.seo_keywords' => 'nullable|string|max:255',
            'translations.*.seo_od_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
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
