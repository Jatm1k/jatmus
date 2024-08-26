<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SongProcessRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'song' => ['sometimes', 'required_without:song_id', File::types(['mp3', 'wav'])->max('20mb')],
            'song_id' => ['sometimes', 'required_without:song', 'integer', 'exists:songs,id'],
            'effect' => ['required', 'string', 'in:speed_up,slowed,8d,bass'],
        ];
    }
}
