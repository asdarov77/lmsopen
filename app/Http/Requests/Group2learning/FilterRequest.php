<?php

namespace App\Http\Requests\Group2learning;

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
            'group_id' => 'int',
            'course_id' => 'int',
            'category_id' => 'int',
            'course' => 'string',
        ];
    }
}
