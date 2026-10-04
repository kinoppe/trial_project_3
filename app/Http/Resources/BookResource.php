<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date?->format('Y-m-d'),
            'description' => $this->description,
            'image_url' => $this->image_url,
            'created_by' => $this->created_by,

            'genres' => $this->whenLoaded('genres', function () {
                return $this->genres->map(fn ($genre) => [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ]);
            }),

            'average_rating' => $this->when(
                isset($this->reviews_avg_rating),
                round((float) $this->reviews_avg_rating, 1)
            ),

            'reviews_count' => $this->when(
                isset($this->reviews_count),
                (int) $this->reviews_count
            ),

            'reviews' => ReviewResource::collection(
                $this->whenLoaded('reviews')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
