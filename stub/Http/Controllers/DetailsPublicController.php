<?php

namespace TypiCMS\Modules\Objects\Http\Controllers;

use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Controllers\BasePublicController;
use TypiCMS\Modules\Objects\Models\ObjectDetail;
use TypiCMS\Modules\Objects\Models\Object;

class DetailsPublicController extends BasePublicController
{
    public function show($slug, $detailSlug): View
    {
        $object = Object::published()->whereSlugIs($slug)->firstOrFail();
        $model = ObjectDetail::published()->whereSlugIs($detailSlug)->where('object_id', $object->id)->firstOrFail();

        return view('parents::public.details.show')
            ->with(compact('model'));
    }
}
