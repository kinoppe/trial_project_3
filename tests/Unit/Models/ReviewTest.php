<?php

namespace Tests\Unit\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    public function test_review_uses_has_factory(): void
    {
        $this->assertContains(
            HasFactory::class,
            class_uses_recursive(Review::class)
        );
    }

    public function test_review_fillable(): void
    {
        $review = new Review();

        $this->assertEquals(
            ['user_id', 'book_id', 'rating', 'comment'],
            $review->getFillable()
        );
    }

    public function test_review_has_liked_by_users_relation(): void
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $review->likedByUsers()
        );

        $this->assertEquals(
            'review_likes',
            $review->likedByUsers()->getTable()
        );

        $this->assertEquals(
            'review_id',
            $review->likedByUsers()->getForeignPivotKeyName()
        );

        $this->assertEquals(
            'user_id',
            $review->likedByUsers()->getRelatedPivotKeyName()
        );
    }

    public function test_review_belongs_to_user(): void
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->user()
        );

        $this->assertEquals(
            'user_id',
            $review->user()->getForeignKeyName()
        );
    }

    public function test_review_belongs_to_book(): void
    {
        $review = new Review();

        $this->assertInstanceOf(
            BelongsTo::class,
            $review->book()
        );

        $this->assertEquals(
            'book_id',
            $review->book()->getForeignKeyName()
        );
    }
}
