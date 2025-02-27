<?php

namespace App\Http\Requests;

use App\Rules\BooleanRule;
use Illuminate\Foundation\Http\FormRequest;

class SuggestionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'statement' => ['required', new BooleanRule],
            'topic' => 'required|string'
        ];
    }
}
