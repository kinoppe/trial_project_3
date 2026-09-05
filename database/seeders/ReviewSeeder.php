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
        $userIds = User::orderBy('id')->pluck('id')->values();
        $books = Book::orderBy('id')->get();

        if ($userIds->count() < 5 || $books->count() < 11) {
            throw new \RuntimeException(
                'ユーザー5件と書籍11件を先に登録してください。'
            );
        }

        /*
         * userは0〜4で指定します。
         * BookSeederで登録した書籍の順番に対応しています。
         */
        $reviewData = [
            // 1. 吾輩は猫である：3件
            [
                ['user' => 0, 'rating' => 5, 'comment' => '猫の視点から描かれる人間社会の様子が面白く、最後まで楽しめました。'],
                ['user' => 1, 'rating' => 4, 'comment' => '古い作品ですが、ユーモアのある文章で今でも読みやすいと感じました。'],
                ['user' => 2, 'rating' => 4, 'comment' => '人間の振る舞いを猫が冷静に観察しているところが印象的でした。'],
            ],

            // 2. 人を動かす：3件
            [
                ['user' => 1, 'rating' => 5, 'comment' => '仕事だけでなく、日常の人間関係にも活用できる内容でした。'],
                ['user' => 2, 'rating' => 4, 'comment' => '相手を尊重することの大切さを改めて学ぶことができました。'],
                ['user' => 3, 'rating' => 5, 'comment' => '具体例が多く、実際のコミュニケーションに取り入れやすいです。'],
            ],

            // 3. リーダブルコード：3件
            [
                ['user' => 2, 'rating' => 5, 'comment' => '変数名や関数の分け方など、すぐに実践できる内容が豊富でした。'],
                ['user' => 3, 'rating' => 5, 'comment' => 'プログラミング初心者にも分かりやすく、何度も読み返したい技術書です。'],
                ['user' => 4, 'rating' => 4, 'comment' => 'チーム開発で読みやすいコードを書く重要性がよく理解できました。'],
            ],

            // 4. 7つの習慣：3件
            [
                ['user' => 0, 'rating' => 5, 'comment' => '自分の行動や考え方を見直すきっかけになりました。'],
                ['user' => 3, 'rating' => 4, 'comment' => '内容は少し難しいですが、じっくり読む価値があります。'],
                ['user' => 4, 'rating' => 5, 'comment' => '仕事と私生活の両方に役立つ考え方が紹介されています。'],
            ],

            // 5. 坊っちゃん：3件
            [
                ['user' => 0, 'rating' => 4, 'comment' => '主人公のまっすぐな性格が爽快で、テンポよく読めました。'],
                ['user' => 1, 'rating' => 4, 'comment' => '登場人物が個性的で、学校内の騒動が面白かったです。'],
                ['user' => 4, 'rating' => 3, 'comment' => '時代を感じる表現はありますが、物語自体は楽しめました。'],
            ],

            // 6. サピエンス全史：3件
            [
                ['user' => 1, 'rating' => 5, 'comment' => '人類の歴史をこれまでとは違う視点で捉えられる一冊でした。'],
                ['user' => 2, 'rating' => 4, 'comment' => '情報量が多いですが、歴史と科学のつながりが興味深かったです。'],
                ['user' => 4, 'rating' => 5, 'comment' => '社会や宗教、お金が生まれた背景の説明がとても面白かったです。'],
            ],

            // 7. Clean Code：3件
            [
                ['user' => 0, 'rating' => 5, 'comment' => 'コードの書き方だけでなく、設計に対する姿勢も学べました。'],
                ['user' => 2, 'rating' => 5, 'comment' => '実務でコードを改善するときの判断基準として役立っています。'],
                ['user' => 3, 'rating' => 4, 'comment' => 'サンプルが豊富で、リファクタリングの考え方を理解できました。'],
            ],

            // 8. 嫌われる勇気：3件
            [
                ['user' => 1, 'rating' => 4, 'comment' => '他人の評価を気にしすぎないという考え方が心に残りました。'],
                ['user' => 3, 'rating' => 5, 'comment' => '対話形式なので読みやすく、自分の悩みと重ねて考えられました。'],
                ['user' => 4, 'rating' => 4, 'comment' => '課題の分離という考え方が、日常生活で特に役立ちそうです。'],
            ],

            // 9. 火花：3件
            [
                ['user' => 0, 'rating' => 4, 'comment' => '芸人として生きる難しさと二人の関係が丁寧に描かれていました。'],
                ['user' => 2, 'rating' => 3, 'comment' => '静かな物語ですが、登場人物の葛藤が伝わってきました。'],
                ['user' => 4, 'rating' => 4, 'comment' => '夢を追い続けることの厳しさを感じさせる作品でした。'],
            ],

            // 10. FACTFULNESS：3件
            [
                ['user' => 0, 'rating' => 5, 'comment' => '思い込みではなくデータを見ることの大切さを学びました。'],
                ['user' => 1, 'rating' => 5, 'comment' => 'クイズ形式の導入が面白く、自分の認識の偏りに気づけました。'],
                ['user' => 3, 'rating' => 4, 'comment' => 'ニュースや統計を見るときの視点が変わる内容でした。'],
            ],

            // 11. コンテナ物語：2件
            [
                ['user' => 2, 'rating' => 4, 'comment' => 'コンテナが世界の物流を変えた過程が詳しく説明されていました。'],
                ['user' => 4, 'rating' => 5, 'comment' => '身近な物流の仕組みに、これほど大きな歴史があるとは驚きました。'],
            ],
        ];

        foreach ($reviewData as $bookIndex => $reviews) {
            foreach ($reviews as $review) {
                Review::create([
                    'user_id' => $userIds[$review['user']],
                    'book_id' => $books[$bookIndex]->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                ]);
            }
        }
    }
}