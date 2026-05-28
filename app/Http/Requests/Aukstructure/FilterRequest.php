<?php

namespace App\Http\Requests\Aukstructure;

use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => 'int',
            'parent_id' => 'int',
            'type' => 'int',
        ];
    }
}
