<?php

namespace Tests\Feature\Ranking;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranking_page_is_displayed(): void
    {
        $response = $this->get(route('ranking.index'));

        $response->assertOk();
        $response->assertViewIs('ranking.index');
        $response->assertViewHas('rankedBooks');
    }

    public function test_books_are_sorted_by_average_rating(): void
    {
        $highBook = Book::factory()->create();
        $middleBook = Book::factory()->create();
        $lowBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $highBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $middleBook->id,
            'rating' => 3,
        ]);

        Review::factory()->create([
            'book_id' => $lowBook->id,
            'rating' => 1,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($books) use (
            $highBook,
            $middleBook,
            $lowBook
        ) {
            return $books->pluck('id')->all() === [
                $highBook->id,
                $middleBook->id,
                $lowBook->id,
            ];
        });
    }

    public function test_books_with_no_reviews_are_excluded(): void
    {
        $reviewedBook = Book::factory()->create();
        $unreviewedBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $reviewedBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($books) use (
            $reviewedBook,
            $unreviewedBook
        ) {
            return $books->count() === 1
                && $books->contains('id', $reviewedBook->id)
                && ! $books->contains('id', $unreviewedBook->id);
        });
    }

    public function test_ranking_displays_maximum_ten_books(): void
    {
        $books = Book::factory()->count(11)->create();

        foreach ($books as $book) {
            Review::factory()->create([
                'book_id' => $book->id,
                'rating' => 5,
            ]);
        }

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($rankedBooks) {
            return $rankedBooks->count() === 10;
        });
    }

    public function test_books_with_same_rating_are_sorted_by_review_count(): void
    {
        $moreReviewsBook = Book::factory()->create();
        $fewerReviewsBook = Book::factory()->create();

        Review::factory()->count(3)->create([
            'book_id' => $moreReviewsBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $fewerReviewsBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($books) use (
            $moreReviewsBook,
            $fewerReviewsBook
        ) {
            return $books->pluck('id')->all() === [
                $moreReviewsBook->id,
                $fewerReviewsBook->id,
            ];
        });
    }

    public function test_books_with_same_rating_and_review_count_are_sorted_by_id(): void
    {
        $firstBook = Book::factory()->create();
        $secondBook = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $firstBook->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'book_id' => $secondBook->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();

        $response->assertViewHas('rankedBooks', function ($books) use (
            $firstBook,
            $secondBook
        ) {
            return $books->pluck('id')->all() === [
                $firstBook->id,
                $secondBook->id,
            ];
        });
    }

    public function test_guest_can_view_ranking(): void
    {
        $book = Book::factory()->create();

        Review::factory()->create([
            'book_id' => $book->id,
            'rating' => 5,
        ]);

        $response = $this->get(route('ranking.index'));

        $response->assertOk();
    }
}
