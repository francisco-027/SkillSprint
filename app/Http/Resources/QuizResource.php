<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'mode'           => $this->mode,
            'difficulty'     => $this->difficulty,
            'question_count' => $this->question_count,
            'created_at'     => $this->created_at,
        ];
    }
}