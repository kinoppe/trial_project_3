<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Carbon\Carbon;

class ProcessReadingPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reading-plans:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '読書計画の期限チェックとリマインダー通知を行う';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        {
            $today = Carbon::today();

            $plans = ReadingPlan::with(['user', 'book'])
                ->where('status', '!=', 'completed')
                ->get();

            foreach ($plans as $plan) {
                $dueDate = Carbon::parse($plan->due_date)->startOfDay();

                if ($dueDate->isSameDay($today->copy()->addDays(3))) {
                    $plan->user->notify(
                        new ReadingPlanReminder($plan, 'three_days_before')
                    );
                }

                if ($dueDate->isSameDay($today)) {
                    $plan->user->notify(
                        new ReadingPlanReminder($plan, 'on_due_date')
                    );
                }

                if ($dueDate->lt($today)) {
                    $plan->update([
                        'status' => 'overdue',
                    ]);
                }

                if ($dueDate->isSameDay($today->copy()->subDays(3))) {
                    $plan->user->notify(
                        new ReadingPlanReminder($plan, 'three_days_after')
                    );
                }
            }

            return Command::SUCCESS;
        }
    }
}
