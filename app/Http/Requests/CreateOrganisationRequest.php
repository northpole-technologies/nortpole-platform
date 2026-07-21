<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrganisationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool
{
    return true;
}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
    'name' => ['required', 'string', 'max:255'],
    'slug' => ['required', 'string', 'max:255', 'unique:organisations,slug'],
    'email' => ['nullable', 'email', 'max:255'],
    'phone' => ['nullable', 'string', 'max:30'],
    'website' => ['nullable', 'url'],
    'country' => ['nullable', 'size:2'],
    'timezone' => ['nullable', 'string'],
];
    }
}
