<?php

namespace App\Http\Requests\Question;

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
            'aukstructure_id' => 'int',
            'category_id' => 'int',
            'id' => 'int',
        ];
    }
}
