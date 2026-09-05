<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::orderBy('id')->get();
        $bookIds = Book::orderBy('id')->pluck('id')->values();

        if ($users->count() < 5 || $bookIds->count() < 11) {
            throw new \RuntimeException(
                'ユーザー5件と書籍11件を先に登録してください。'
            );
        }

        $favorites = [
            [0, 2, 5],       // 山田太郎：3冊
            [1, 3, 7, 9],    // 鈴木花子：4冊
            [2, 5, 6, 9, 10],// 田中一郎：5冊
            [0, 3, 7],       // 佐藤美咲：3冊
            [4, 5, 8, 10],   // 高橋健太：4冊
        ];

        foreach ($users->take(5) as $userIndex => $user) {
            $favoriteBookIds = collect($favorites[$userIndex])
                ->map(fn (int $bookIndex) => $bookIds[$bookIndex])
                ->all();

            $user->favoriteBooks()
                ->syncWithoutDetaching($favoriteBookIds);
        }
    }
}