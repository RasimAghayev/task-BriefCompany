<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProductRequest extends FormRequest
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
            "name" => "required",
            "priceFrom" => "required|regex:/^\d{1,13}(\.\d{1,4})?$/",
            "priceTo" => "required|regex:/^\d{1,13}(\.\d{1,4})?$/",
            "published" => "required|in:Yes,No",
            "categories" =>"required| array|min:2|max:10"
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required'     => 'A name for the product is required.',
            'priceFrom.required'     => 'A price From for the product is required.',
            'priceFrom.regex'     => 'A price From for the product is only number.',
            'priceTo.required'     => 'A price To for the product is required.',
            'priceTo.regex'     => 'A price To for the product is only number.',
            'published.required'     => 'A published for the product is required.',
            'published.in'     => 'A published for the product is only Yes or No.',
            'categories.required'     => 'A categories for the product is required.',
            'categories.array'     => 'A categories for the product is only array.',
        ];
    }
    /**
     * [failedValidation [Overriding the event validator for custom error response]]
     * @param  Validator $validator [description]
     * @return array
     */
    public function failedValidation(Validator $validator) {
        $data=[
            'success' => false,
            'message' => 'Validator',
            'data' => $validator->errors()
        ];
        throw new HttpResponseException(response()->json($data, 422));
    }
}
