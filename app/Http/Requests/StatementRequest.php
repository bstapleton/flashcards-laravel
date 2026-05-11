<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StatementRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isUpdateRequest();

        return [
            'text' => 'required|string',
            'is_true' => 'required|boolean',
            'explanation' => 'nullable|string',
            'subjects' => 'nullable|array',
            'subjects.*' => 'exists:tags,id',
            'publish_after_update' => $isUpdate ? 'nullable|boolean' : 'sometimes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // Convert radio button values to boolean
        if (isset($data['is_true'])) {
            $data['is_true'] = (bool) $data['is_true'];
        }

        $this->merge($data);
    }

    /**
     * Check if this is an update request based on HTTP method.
     */
    private function isUpdateRequest(): bool
    {
        return in_array($this->method(), ['PATCH', 'PUT']);
    }
}
