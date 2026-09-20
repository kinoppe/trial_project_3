<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewLikeController extends Controller
{
    public function toggle(Review $review)
    {
        $user = auth()->user();

        $isLiked = $user->likedReviews()
            ->whereKey($review->id)
            ->exists();

        if ($isLiked) {
            $user->likedReviews()->detach($review->id);
            $message = 'レビューのいいねを解除しました。';
        } else {
            $user->likedReviews()->attach($review->id);
            $message = 'レビューにいいねしました。';
        }

        return back()->with('success', $message);
    }
}
