<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $summary = [
            'total_reviews' => Review::where('user_id', $userId)->count(),

            'books_read' => Review::where('user_id', $userId)
                ->distinct()
                ->count('book_id'),

            'average_rating' => Review::where('user_id', $userId)
                ->avg('rating') ?? 0,
        ];

        $ratingCounts = Review::where('user_id', $userId)
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        $ratingDistribution = collect(range(1, 5))
            ->map(function ($rating) use ($ratingCounts) {
                return (int) ($ratingCounts[$rating] ?? 0);
            });

        $topRatedBooks = Review::with('book:id,title,author')
            ->where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->orderByDesc('rating')
            ->latest()
            ->limit(5)
            ->get()
            ->filter(fn ($review) => $review->book !== null)
            ->values()
            ->map(function ($review) {
                return [
                    'id' => $review->book->id,
                    'title' => $review->book->title,
                    'author' => $review->book->author,
                    'rating' => (int) $review->rating,
                ];
            });

        $genreRatings = Genre::query()
            ->select('genres.id', 'genres.name')
            ->join(
                'book_genre',
                'book_genre.genre_id',
                '=',
                'genres.id'
            )
            ->join(
                'reviews',
                'reviews.book_id',
                '=',
                'book_genre.book_id'
            )
            ->where('reviews.user_id', $userId)
            ->selectRaw('AVG(reviews.rating) as average_rating')
            ->selectRaw('COUNT(reviews.id) as review_count')
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->limit(5)
            ->get()
            ->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                    'average_rating' => (float) $genre->average_rating,
                    'count' => (int) $genre->review_count,
                ];
            });

        $stats = [
            'summary' => $summary,
            'rating_distribution' => $ratingDistribution,
            'top_rated_books' => $topRatedBooks,
            'genre_ratings' => $genreRatings,
        ];

        return view('reports.index', compact('stats'));
    }
}