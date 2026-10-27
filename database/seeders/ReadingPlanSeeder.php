<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReadingPlan;
use Carbon\Carbon;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $today = Carbon::today();

        // 山田太郎：期限3日前の通知対象
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 1,
            'target_date' => $today->copy()->addDays(3),
            'status' => 'in_progress',
        ]);

        // 山田太郎：期限当日の通知対象
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 2,
            'target_date' => $today->copy(),
            'status' => 'in_progress',
        ]);

        // 山田太郎：期限3日超過・自動期限切れ対象
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 3,
            'target_date' => $today->copy()->subDays(3),
            'status' => 'in_progress',
        ]);

        // 山田太郎：通知対象外
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 4,
            'target_date' => $today->copy()->addDays(7),
            'status' => 'in_progress',
        ]);

        // 山田太郎：読了済み
        ReadingPlan::create([
            'user_id' => 1,
            'book_id' => 5,
            'target_date' => $today->copy()->subDays(10),
            'completed_at' => $today->copy()->subDays(5),
            'status' => 'completed',
        ]);

        // 鈴木花子：他ユーザーの認可確認用
        ReadingPlan::create([
            'user_id' => 2,
            'book_id' => 6,
            'target_date' => $today->copy()->addDays(5),
            'status' => 'in_progress',
        ]);
    }
}
