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
                Rule::unique('reading_plans', 'book_id')
                    ->where(fn ($query) => $query
                        ->where('user_id', $this->user()->id)
                        ->whereIn('status', ['planned', 'overdue'])
                    ),
            ],
            'target_date' => ['required','date','after_or_equal:today',],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.integer' => '書籍IDは整数で指定してください。',
            'book_id.exists' => '選択された書籍は存在しません。',
            'book_id.unique' => 'この書籍はすでに進行中の読書計画へ登録されています。',
            'target_required' => '期日は必須です。',
            'target_date' =>'期日は有効な日付形式で指定してください。',
            'target_date.after_or_equal' => '期日は今日以降の日付を指定してください。',
        ];
    }
}