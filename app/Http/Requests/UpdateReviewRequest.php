<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReviewRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => ['required','integer','between:1,5',],
            'comment' => ['required','string','max:1000',],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を選択してください。',
            'rating.integer' => '評価を正しく選択してください。',
            'rating.between' => '評価は1から5で選択してください。',
            'comment.required' => 'レビューを入力してください。',
            'comment.max' => 'レビューは1000文字以内で入力してください。',
        ];
    }
}
