<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeriesDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $this->resource = (object) $this->resource;

        $metaValues = [];
        if (isset($this->resource->meta) && is_array($this->resource->meta)) {
            $metaValues = array_column($this->resource->meta, 'meta_value', 'meta_key');
        }

        return [
            'title' => $this->resource->post_title,
            'cover' => $this->resource->cover,
            'badge' => isset($metaValues['ero_hot']) ? "Hot" : null,
            'rating' => $metaValues['ero_score'],
            'status' => $metaValues['ero_status'],
            'serialization' => $metaValues['ero_serialization'],
            'genre' => $this->resource->genres,
            'chapters' => $this->resource->chapters
        ];
    }
}
