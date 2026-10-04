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
    public function index(Request $request)
    {
        $keyword = $request->input('keyword');
        $genreId = $request->input('genre');
        $sort = $request->input('sort', 'latest');

        if (! in_array($sort, ['latest', 'oldest', 'title', 'rating'], true)) {
            $sort = 'latest';
        }

        $books = Book::query()
            ->with('genres')
            ->withAvg('reviews', 'rating')
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('author', 'like', '%' . $keyword . '%');
                });
            })
            ->when($genreId, function ($query, $genreId) {
                $query->whereHas('genres', function ($query) use ($genreId) {
                    $query->where('genres.id', $genreId);
                });
            });

        switch ($sort) {
            case 'oldest':
                $books->oldest();
                break;

            case 'title':
                $books->orderBy('title');
                break;

            case 'rating':
                // レビューなし（平均評価がNULL）の書籍を最後に表示
                $books->orderByRaw('reviews_avg_rating IS NULL')
                    ->orderByDesc('reviews_avg_rating')
                    ->latest('books.created_at');
                break;

            case 'latest':
            default:
                $books->latest();
                break;
        }

        $books = $books
            ->paginate(10)
            ->withQueryString();

        $genres = Genre::orderBy('name')->get();

        return view('books.index', compact(
            'books',
            'genres',
            'keyword',
            'genreId',
            'sort'
        ));
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
                'user_id'   => $request->user()->id,
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
