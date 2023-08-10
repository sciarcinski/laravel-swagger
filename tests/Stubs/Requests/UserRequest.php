<?php

namespace Sciarcinski\LaravelSwagger\Tests\Stubs\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'test1.test2.test3.test4_1' => ['required', 'numeric'],
            'test1.test2.test3.test4_2.test5' => ['required', 'numeric'],
            'test1.test2.test3.test4_3.*' => ['required', 'numeric'],
            'test1.test2.test3.test4_4' => ['nullable'],
            'type' => ['required', Rule::in(['test1', 'test2'])],
        ];
    }
}
