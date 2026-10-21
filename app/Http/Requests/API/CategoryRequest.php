<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            // The parent of a category has to exist, otherwise the category
            // would point at nothing.
            'parent_id' => ['sometimes', 'nullable', 'integer', 'exists:categories,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'name.required' => 'A name for the category is required.',
            'name.max' => 'The name of the category is too long.',
            'parent_id.exists' => 'The parent of the category does not exist.',
        ];
    }

    /**
     * [failedValidation [Overriding the event validator for custom error response]]
     *
     * @return array
     */
    public function failedValidation(Validator $validator)
    {
        $data = [
            'success' => false,
            'message' => 'Validator',
            'data' => $validator->errors(),
        ];

        throw new HttpResponseException(response()->json($data, 422));
    }
}
