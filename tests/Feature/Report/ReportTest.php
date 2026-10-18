<?php

namespace Tests\Feature\Report;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_report(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();
        $response->assertViewIs('reports.index');
        $response->assertViewHas('stats');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('reports.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_report_summary_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 2
                && $stats['summary']['books_read'] === 2
                && (float) $stats['summary']['average_rating'] === 4.0;
        });
    }

    public function test_rating_distribution_is_calculated_correctly(): void
    {
        $user = User::factory()->create();

        foreach ([1, 2, 2, 4, 5] as $rating) {
            Review::factory()->create([
                'user_id' => $user->id,
                'rating' => $rating,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['rating_distribution']->all() === [
                1, 2, 0, 1, 1,
            ];
        });
    }

    public function test_top_rated_books_are_sorted_and_limited_to_five(): void
    {
        $user = User::factory()->create();

        foreach ([5, 5, 5, 5, 5, 4, 3] as $rating) {
            Review::factory()->create([
                'user_id' => $user->id,
                'rating' => $rating,
            ]);
        }

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            $books = $stats['top_rated_books'];

            return $books->count() === 5
                && $books->every(fn ($book) => $book['rating'] === 5);
        });
    }

    public function test_genre_ratings_are_calculated_correctly(): void
    {
        $user = User::factory()->create();

        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'rating' => 3,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) use ($genre1, $genre2) {
            $genres = $stats['genre_ratings'];

            return $genres->count() === 2
                && $genres[0]['id'] === $genre1->id
                && $genres[0]['average_rating'] === 5.0
                && $genres[0]['count'] === 1
                && $genres[1]['id'] === $genre2->id
                && $genres[1]['average_rating'] === 3.0
                && $genres[1]['count'] === 1;
        });
    }

    public function test_other_users_reviews_are_excluded(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Review::factory()->create([
            'user_id' => $user->id,
            'rating' => 5,
        ]);

        Review::factory()->create([
            'user_id' => $otherUser->id,
            'rating' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 1
                && (float) $stats['summary']['average_rating'] === 5.0
                && $stats['rating_distribution']->all() === [
                    0, 0, 0, 0, 1,
                ];
        });
    }

    public function test_report_displays_correctly_without_reviews(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports.index'));

        $response->assertOk();

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 0
                && $stats['summary']['books_read'] === 0
                && (float) $stats['summary']['average_rating'] === 0.0
                && $stats['rating_distribution']->all() === [
                    0, 0, 0, 0, 0,
                ]
                && $stats['top_rated_books']->isEmpty()
                && $stats['genre_ratings']->isEmpty();
        });
    }
}
