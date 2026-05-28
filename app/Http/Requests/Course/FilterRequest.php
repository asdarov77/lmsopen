<?php

namespace App\Http\Requests\Course;

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
            'title' => 'string',
            'path' => 'string',
            'aircraft_id' => 'int',
            'category_id' => 'int',
            'parent_id' => 'int',
            'type_id' => 'int',
            'course_id' => 'int',
        ];
    }
}
