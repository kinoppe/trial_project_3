<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Enums\ReadingPlanStatus;

class ReadingPlanController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:planned,completed,overdue'],
        ]);

        $currentStatus = $validated['status'] ?? null;

        $readingPlans = $request->user()
            ->readingPlans()
            ->with('book')
            ->when(
                $currentStatus,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->orderBy('target_date')
            ->paginate(10)
            ->withQueryString();

        return view('reading-plans.index', compact('readingPlans','currentStatus'));
    }

    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    public function store(StoreReadingPlanRequest $request): RedirectResponse {
        $request->user()->readingPlans()->create([
            'book_id' => $request->validated('book_id'),
            'target_date' => $request->validated('target_date'),
            'status' => ReadingPlanStatus::Planned,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を登録しました。');
    }

    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    public function update(UpdateReadingPlanRequest $request,ReadingPlan $readingPlan): RedirectResponse {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'target_date' => $request->validated('target_date'),
            'status' => ReadingPlanStatus::Planned,
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    public function complete(ReadingPlan $readingPlan): RedirectResponse {
        $this->authorize('complete', $readingPlan);

        if ($readingPlan->status === ReadingPlanStatus::Completed) {
            return back()->with('success', 'この書籍はすでに読了済みです。');
        }

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return back()->with('success', '読了として記録しました。');
    }

    public function destroy(
        ReadingPlan $readingPlan
    ): RedirectResponse {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }
}