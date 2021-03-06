<?php

namespace TypiCMS\Modules\Objects\Http\Requests;

use TypiCMS\Modules\Core\Http\Requests\AbstractFormRequest;

class DetailFormRequest extends AbstractFormRequest
{
    public function rules()
    {
        return [
            'image_id' => 'nullable|integer',
            'title.*' => 'nullable|max:255',
            'slug.*' => 'nullable|alpha_dash|max:255|required_if:status.*,1|required_with:title.*',
            'status.*' => 'boolean',
            'summary.*' => 'nullable',
            'body.*' => 'nullable',
            'object_id' => 'required',
        ];
    }
}
