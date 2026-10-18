<?php

namespace Tests\Unit\Models;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Models\Book;
use App\Enums\ReadingPlanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    public function test_reading_plan_uses_has_factory(): void
    {
        $this->assertContains(
            HasFactory::class,
            class_uses_recursive(ReadingPlan::class)
        );
    }

    public function test_reading_plan_fillable(): void
    {
        $readingPlan = new ReadingPlan();

        $this->assertEquals(
            [
                'user_id',
                'book_id',
                'target_date',
                'status',
                'completed_at',
            ],
            $readingPlan->getFillable()
        );
    }

    public function test_reading_plan_status_constants(): void
    {
        $this->assertEquals(
            'planned',
            ReadingPlan::STATUS_PLANNED
        );

        $this->assertEquals(
            'completed',
            ReadingPlan::STATUS_COMPLETED
        );

        $this->assertEquals(
            'overdue',
            ReadingPlan::STATUS_OVERDUE
        );
    }

    public function test_reading_plan_casts(): void
    {
        $readingPlan = new ReadingPlan();

        $this->assertEquals(
            'date',
            $readingPlan->getCasts()['target_date']
        );

        $this->assertEquals(
            'datetime',
            $readingPlan->getCasts()['completed_at']
        );

        $this->assertEquals(
            ReadingPlanStatus::class,
            $readingPlan->getCasts()['status']
        );
    }

    public function test_reading_plan_belongs_to_user(): void
    {
        $readingPlan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $readingPlan->user()
        );

        $this->assertEquals(
            User::class,
            get_class($readingPlan->user()->getRelated())
        );

        $this->assertEquals(
            'user_id',
            $readingPlan->user()->getForeignKeyName()
        );
    }

    public function test_reading_plan_belongs_to_book(): void
    {
        $readingPlan = new ReadingPlan();

        $this->assertInstanceOf(
            BelongsTo::class,
            $readingPlan->book()
        );

        $this->assertEquals(
            Book::class,
            get_class($readingPlan->book()->getRelated())
        );

        $this->assertEquals(
            'book_id',
            $readingPlan->book()->getForeignKeyName()
        );
    }
}
