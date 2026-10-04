<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreBookRequest;
use App\Http\Requests\Api\UpdateBookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
        'keyword' => ['nullable', 'string', 'max:255'],
        'genre_id' => ['nullable', 'integer', 'exists:genres,id'],
        'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
    ]);

    $books = Book::query()
        ->with('genres')
        ->withAvg('reviews', 'rating')
        ->withCount('reviews')
        ->when($validated['keyword'] ?? null, function ($query, $keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%")
                    ->orWhere('isbn', 'like', "%{$keyword}%");
            });
        })
        ->when($validated['genre_id'] ?? null, function ($query, $genreId) {
            $query->whereHas('genres', function ($query) use ($genreId) {
                $query->where('genres.id', $genreId);
            });
        })
        ->latest()
        ->paginate($validated['per_page'] ?? 10)
        ->withQueryString();

    return BookResource::collection($books);
    }

    public function show(Book $book): BookResource
    {
        $book->load([
        'genres',
        'reviews.user',
        ]);

        $book->loadAvg('reviews', 'rating');
        $book->loadCount('reviews');

        return new BookResource($book);
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated, $request) {
            $book = Book::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            $book->genres()->sync($validated['genre_ids'] ?? []);

            return $book;
        });

        $book->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return (new BookResource($book))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateBookRequest $request,Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $book) {
            $book->update([
                'title' => $validated['title'],
                'author' => $validated['author'],
                'isbn' => $validated['isbn'],
                'published_date' => $validated['published_date'] ?? null,
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genre_ids'] ?? []);
        });

        $book->refresh()
            ->load('genres')
            ->loadAvg('reviews', 'rating')
            ->loadCount('reviews');

        return new BookResource($book);
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        $book->delete();

        return response()->noContent();
    }
}