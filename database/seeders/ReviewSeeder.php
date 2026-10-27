<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = Book::all();

        if ($users->count() < 5 || $books->count() < 11) {
            throw new \RuntimeException(
                'ユーザー5件と書籍11件を先に登録してください。'
            );
        }

        // 評価別コメントテンプレート
        $comments = [
            5 => [
                '素晴らしい本でした！',
                '人生が変わりました。',
                '何度も読み返しています。',
            ],
            4 => [
                'とても参考になりました。',
                '読みやすくておすすめです。',
                '期待通りの内容でした。',
            ],
            3 => [
                '普通でした。',
                '可もなく不可もなく。',
                '期待したほどではなかった。',
            ],
            2 => [
                '少し期待外れでした。',
                '内容が薄い印象。',
                'もう少し深掘りしてほしかった。',
            ],
            1 => [
                '残念ながら合いませんでした。',
                '期待と違いました。',
            ],
        ];

        foreach ($books as $book) {
            // 各書籍に2〜4件のレビュー
            $reviewCount = rand(2, 4);

            // 投稿者をランダムに選択
            $reviewUsers = $users->random($reviewCount);

            foreach ($reviewUsers as $user) {
                // 評価を1〜5からランダムに選択
                $rating = rand(1, 5);

                Review::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                    ],
                    [
                        'rating' => $rating,
                        'comment' => collect($comments[$rating])->random(),
                    ]
                );
            }
        }
    }
}