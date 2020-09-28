<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetMoviesListRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => 'string|min:1|required_if:filter,query',
            'page' => 'integer|min:1|max:1000',
            'filter' => 'in:popularity,release,query'
        ];
    }
}
