<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class FavoriteController extends Controller
{
    public function toggle(Book $book)
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
