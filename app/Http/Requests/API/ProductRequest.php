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
            'name' => ['required', 'string', 'max:100'],
            'priceFrom' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,13}(\.\d{1,4})?$/'],
            'priceTo' => ['required', 'numeric', 'min:0', 'regex:/^\d{1,13}(\.\d{1,4})?$/'],
            'published' => ['required', 'string', 'in:Yes,No'],
            'categories' => ['required', 'array', 'min:2', 'max:10'],
            // Every category of the request has to exist, otherwise the
            // product would be written with a relation to nothing.
            'categories.*' => ['required', 'integer', 'distinct', 'exists:categories,id'],
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
            'name.required' => 'A name for the product is required.',
            'name.max' => 'The name of the product is too long.',
            'priceFrom.required' => 'A price From for the product is required.',
            'priceFrom.regex' => 'A price From for the product is only number.',
            'priceTo.required' => 'A price To for the product is required.',
            'priceTo.regex' => 'A price To for the product is only number.',
            'published.required' => 'A published for the product is required.',
            'published.in' => 'A published for the product is only Yes or No.',
            'categories.required' => 'A categories for the product is required.',
            'categories.array' => 'A categories for the product is only array.',
            'categories.min' => 'A product needs at least two categories.',
            'categories.max' => 'A product can have at most ten categories.',
            'categories.*.exists' => 'One of the categories does not exist.',
            'categories.*.distinct' => 'The same category can not be used twice.',
        ];
    }

    /**
     * The upper price of a product has to be at least the lower price.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $from = $this->input('priceFrom');
            $to = $this->input('priceTo');

            if (! is_numeric($from) || ! is_numeric($to)) {
                return;
            }

            if ((float) $to < (float) $from) {
                $validator->errors()->add(
                    'priceTo',
                    'The upper price of the product has to be at least the lower price.'
                );
            }
        });
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
