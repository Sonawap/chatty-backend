<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateGroupRequest extends FormRequest
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

    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors()->getMessages(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['nullable', Rule::unique('groups')->ignore($this->group)],
            'description' => 'nullable|string',
            'avatar' => 'nullable|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Group Name is required',
            'name.unique' => 'Group Name has already been registered',
            'description.required' => 'Group Description is Required',
        ];
    }
}
