<?php

namespace Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'phone' => ['required', 'string'],
            'email' => ['required', 'string'],
            'password' => ['nullable', 'string'],
            'test1_1' => ['required', 'array'],
            'test1_1.test1_2' => ['required', 'array'],
            'test1_1.test1_2.test1_3' => ['required', 'numeric'],
            'test2_1' => ['required', 'array'],
            'test2_1.test2_2' => ['required', 'array'],
            'test2_1.test2_2.test2_3.*' => ['required', 'string'],
            'test3_1.*' => ['required', 'array', 'numeric'],
        ];
    }
}
