<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'     => 'required|in:text,file,url,sample',
            'text'     => 'required_if:type,text|nullable|string|min:50|max:50000',
            'file'     => 'required_if:type,file|nullable|file|mimes:pdf,docx,txt|max:10240',
            'url'      => 'required_if:type,url|nullable|url|max:2048',
            'skill_id' => 'required_if:type,sample|nullable|integer|exists:skills,id',
        ];
    }
}