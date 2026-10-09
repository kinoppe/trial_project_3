<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required','integer','exists:books,id',
                Rule::unique('reading_plans')
                    ->where(fn ($query) => $query->where(
                        'user_id',
                        $this->user()->id
                    )),
            ],
            'target_date' => ['required','date','after_or_equal:today',],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍が存在しません。',
            'book_id.unique' => 'この書籍はすでに読書計画へ登録されています。',
            'target_date.required' => '期日を入力してください。',
            'target_date.after_or_equal' => '期日には本日以降の日付を指定してください。',
        ];
    }
}