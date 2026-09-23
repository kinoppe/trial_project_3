<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(Request $request): View
    {
        $books = $request->user()
            ->favoriteBooks()
            ->latest('favorites.created_at')
            ->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Request $request, Book $book)
    {
        $user = auth()->user();

        $isFavorite = $user->favoriteBooks()
            ->whereKey($book->id)
            ->exists();

        if ($isFavorite) {
            $user->favoriteBooks()->detach($book->id);
            $message = 'お気に入りを解除しました。';
        } else {
            $user->favoriteBooks()->attach($book->id);
            $message = 'お気に入りに追加しました。';
        }

        return back()->with('success', $message);
    }
}
