<?php

namespace Modules\Comments\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommentCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => 'required|min:3|max:1000',
            'user_id' => 'required|uuid',
            'entity_id' => 'required|uuid',
            'entity_type' => 'required|string',
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
