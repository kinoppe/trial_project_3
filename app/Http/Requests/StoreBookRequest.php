<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'title' => ['required','string','max:255',],

            'author' => ['required','string','max:255',],

            'isbn' => ['required','string','max:13','unique:books,isbn',],

            'published_date' => ['date'],

            'description' => ['nullable','string',],

            'image_url' => ['nullable','url','max:255',],

            'genres' => ['required','array',],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => '書籍タイトルは必須です。',
            'title.string' => '書籍タイトルは文字列で入力してください。',
            'title.max' => '書籍タイトルは255文字以内で入力してください。',

            'author.required' => '著者名は必須です。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は255文字以内で入力してください。',

            'isbn.required' => 'ISBNを入力してください。',
            'isbn.string' => 'ISBNは文字列で入力してください。',
            'isbn.max' => 'ISBNは13桁で入力してください。',
            'isbn.unique' => 'このISBNはすでに登録されています。',

            'published_date.date' => '出版日は有効な日付形式で入力してください。',

            'description.string' => '説明は文字列で入力してください。',

            'image_url.url' => '画像URLは有効なURL形式で入力してください。',
            'image_url.max' => '画像URLは255文字以内で入力してください。',

            'genres.required' => 'ジャンルは1つ以上選択してください。',
            'genres.array' => 'ジャンルの指定が正しくありません。',
        ];
    }

    /**
     * 項目名の日本語表示
     */
    public function attributes(): array
    {
        return [
            'title' => '書籍タイトル',
            'author' => '著者名',
            'isbn' => 'ISBN',
            'published_date' => '出版日',
            'description' => '説明',
            'image_url' => '画像URL',
            'genres' => 'ジャンル',
            'genres.*' => 'ジャンル',
        ];
    }
}
