<?php

namespace Modules\Seo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeoPageCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'url' => 'required|string|max:255',
            'translations' => 'array',
            'translations.*' => 'array',
            'translations.*.seo_title' => 'nullable|string|max:60',
            'translations.*.seo_description' => 'nullable|string|max:160',
            'translations.*.seo_keywords' => 'nullable|string|max:255',
            'translations.*.seo_od_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
