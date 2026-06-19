<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'difficulty'        => $this->difficulty,
            'estimated_minutes' => $this->estimated_minutes,
            'source_filename'   => $this->source_filename,
            'content_sections'  => $this->content_sections,
            'key_terms'         => $this->key_terms,
            'timeline_steps'    => $this->timeline_steps,
            'created_at'        => $this->created_at,
        ];
    }
}