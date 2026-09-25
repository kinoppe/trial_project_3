<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with('genres')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate(10);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        $genres = Genre::orderBy('name')->get();
        return view('books.create', compact('genres'));
    }

    public function store(StoreBookRequest $request)
    {
        $validated = $request->validated();

        $book = DB::transaction(function () use ($validated) {
            $book = Book::create([
                'title'        => $validated['title'],
                'author'       => $validated['author'],
                'isbn'         => $validated['isbn'],
                'published_date' => $validated['published_date'] ?? null,
                'description'  => $validated['description'] ?? null,
                'image_url'    => $validated['image_url'] ?? null,
                'user_id'   => auth()->id(),
            ]);

            $book->genres()->sync($validated['genres']);

            return $book;
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍を登録しました。');
    }

    public function show(Book $book)
    {
        $book->load([
            'genres',
            'reviews.user',
            'reviews.likedByUsers',
        ]);

        $book->loadCount([
            'favoritedUsers',
            'reviews',
        ]);

        $book->reviews->loadCount('likedByUsers');

        $isFavorite = auth()->check()
            ? auth()->user()->favoriteBooks()->whereKey($book->id)->exists()
            : false;

        return view('books.show', compact(
            'book',
            'isFavorite'
        ));
    }

    public function edit(Book $book)
    {
        $this->authorize('update', $book);

        $genres = Genre::orderBy('name')->get();

        $book->load('genres');

        return view('books.edit', compact('book', 'genres'));
    }

    public function update(UpdateBookRequest $request, Book $book)
    {
        $this->authorize('update', $book);

        $validated = $request->validated();

        DB::transaction(function () use ($book, $validated) {
            $book->update([
                'title'        => $validated['title'],
                'author'       => $validated['author'],
                'isbn'         => $validated['isbn'],
                'published_date' => $validated['published_date'],
                'description'  => $validated['description'] ?? null,
                'image_url'    => $validated['image_url'] ?? null,
            ]);

            $book->genres()->sync($validated['genres']);
        });

        return redirect()
            ->route('books.show', $book)
            ->with('success', '書籍情報を更新しました。');
    }

    public function destroy(Book $book)
    {
        $this->authorize('delete', $book);

        DB::transaction(function () use ($book) {
            $book->favoritedUsers()->detach();
            $book->genres()->detach();

            $book->reviews->each(function ($review) {
                $review->likedByUsers()->detach();
                $review->delete();
            });

            $book->delete();
        });

        return redirect()
            ->route('books.index')
            ->with('success', '書籍を削除しました。');
    }
}
