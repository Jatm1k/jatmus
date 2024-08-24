<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SongProcessRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'song' => ['required', File::types(['mp3', 'wav'])->max('20mb')],
            'effect' => ['required', 'string', 'in:speed_up,slowed,8d,bass'],
        ];
    }
}
