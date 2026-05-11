<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MultipleChoiceRequest extends FormRequest
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
        $isDraft = $this->isDraftRequest();
        $isUpdate = $this->isUpdateRequest();

        return [
            'text' => 'required|string',
            'answers' => $this->getAnswersRule($isDraft),
            'answers.*.text' => $isDraft ? 'required_with:answers|string' : 'required|string',
            'answers.*.is_correct' => $isDraft ? 'required_with:answers|boolean' : 'required|boolean',
            'answers.*.explanation' => 'nullable|string',
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

        // Convert checkbox 'on' values to boolean true
        if (isset($data['answers']) && is_array($data['answers'])) {
            $data['answers'] = array_filter($data['answers'], function ($answer) {
                // Filter out answers with empty text
                return ! empty($answer['text']);
            });

            // Convert checkbox values to boolean for is_correct
            foreach ($data['answers'] as &$answer) {
                $answer['is_correct'] = isset($answer['is_correct']) && (
                    $answer['is_correct'] === 'on' ||
                    $answer['is_correct'] === '1' ||
                    $answer['is_correct'] === true ||
                    $answer['is_correct'] === 'true'
                );
            }
        }

        $this->merge($data);
    }

    /**
     * Get the validation rule for answers based on request type.
     */
    private function getAnswersRule(bool $isDraft): string
    {
        if ($isDraft) {
            return 'nullable|array';
        }

        return 'required|array|min:2';
    }

    /**
     * Check if this is a draft request based on route name.
     */
    private function isDraftRequest(): bool
    {
        return str_contains($this->route()->getName(), 'draft');
    }

    /**
     * Check if this is an update request based on HTTP method.
     */
    private function isUpdateRequest(): bool
    {
        return in_array($this->method(), ['PATCH', 'PUT']);
    }
}
