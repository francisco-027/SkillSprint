<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dyslexia_font'   => 'nullable|boolean',
            'high_contrast'   => 'nullable|boolean',
            'reduce_motion'   => 'nullable|boolean',
            'font_size'       => 'nullable|in:small,medium,large,xlarge',
            'line_height'     => 'nullable|numeric|between:1,3',
            'letter_spacing'  => 'nullable|numeric|between:0,5',
            'word_spacing'    => 'nullable|numeric|between:0,10',
            'daily_goal_minutes' => 'nullable|integer|min:5|max:240',
        ];
    }
}