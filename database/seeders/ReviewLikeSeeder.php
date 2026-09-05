<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::orderBy('id')->pluck('id')->values();
        $reviews = Review::orderBy('id')->get();

        foreach ($reviews as $index => $review) {
            // 0、1、2、3人を順番に割り当てる
            $likeCount = $index % 4;

            if ($likeCount === 0) {
                continue;
            }

            $likerIds = $userIds
                ->reject(fn (int $userId) => $userId === $review->user_id)
                ->values()
                ->take($likeCount)
                ->all();

            $review->likedUsers()
                ->syncWithoutDetaching($likerIds);
        }
    }
}