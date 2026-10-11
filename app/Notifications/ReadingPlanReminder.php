<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ReadingPlan;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private ReadingPlan $readingPlan,private string $timing)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->getTitle(),
            'body' => $this->getBody(),
            'timing' => $this->timing,
            'reading_plan_id' => $this->readingPlan->id,
            'book_id' => $this->readingPlan->book_id,
        ];
    }

    private function getTitle(): string
    {
        return match ($this->timing) {
            'three_days_before' => '読書期限が近づいています',
            'on_due_date' => '今日は読書期限です',
            'three_days_after' => '読書期限を過ぎています',
            default => '読書計画のお知らせ',
        };
    }

    private function getBody(): string
    {
        $bookTitle = $this->readingPlan->book->title;

        return match ($this->timing) {
            'three_days_before' =>
                "「{$bookTitle}」の読書期限まであと3日です。",

            'on_due_date' =>
                "「{$bookTitle}」の読書期限は今日です。",

            'three_days_after' =>
                "「{$bookTitle}」の読書期限を3日過ぎています。",

            default =>
                "「{$bookTitle}」の読書計画を確認してください。",
        };
    }
}
