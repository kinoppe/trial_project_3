<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_is_authenticatable(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            Authenticatable::class,
            $user
        );
    }

    public function test_user_fillable(): void
    {
        $user = new User();

        $this->assertEquals(
            ['name', 'email', 'password'],
            $user->getFillable()
        );
    }

    public function test_user_hidden(): void
    {
        $user = new User();

        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
    }

    public function test_user_casts(): void
    {
        $user = new User();

        $this->assertEquals(
            'datetime',
            $user->getCasts()['email_verified_at']
        );

        $this->assertEquals(
            'hashed',
            $user->getCasts()['password']
        );
    }

    public function test_user_has_favorite_books_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->favoriteBooks()
        );

        $this->assertEquals(
            'favorites',
            $user->favoriteBooks()->getTable()
        );
    }

    public function test_user_has_liked_reviews_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            BelongsToMany::class,
            $user->likedReviews()
        );

        $this->assertEquals(
            'review_likes',
            $user->likedReviews()->getTable()
        );
    }

    public function test_user_has_books_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->books()
        );

        $this->assertEquals(
            'user_id',
            $user->books()->getForeignKeyName()
        );
    }

    public function test_user_has_reading_plans_relation(): void
    {
        $user = new User();

        $this->assertInstanceOf(
            HasMany::class,
            $user->readingPlans()
        );

        $this->assertEquals(
            'user_id',
            $user->readingPlans()->getForeignKeyName()
        );
    }
}
