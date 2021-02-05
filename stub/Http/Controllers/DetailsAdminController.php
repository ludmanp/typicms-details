<?php

namespace TypiCMS\Modules\Objects\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use TypiCMS\Modules\Core\Http\Controllers\BaseAdminController;
use TypiCMS\Modules\Objects\Http\Requests\DetailFormRequest;
use TypiCMS\Modules\Objects\Models\Object;
use TypiCMS\Modules\Objects\Models\ObjectDetail;

class DetailsAdminController extends BaseAdminController
{
    public function create(Object $object): View
    {
        $model = new ObjectDetail();

        return view('objects::admin.details.create')
            ->with(compact('model', 'object'));
    }

    public function edit(Object $object, ObjectDetail $detail): View
    {
        return view('parents::admin.details.edit')
            ->with(['model' => $detail, 'object' => $object]);
    }

    public function store(Object $object, DetailFormRequest $request): RedirectResponse
    {
        $detail = ObjectDetail::create($request->validated());

        return $this->redirect($request, $detail);
    }

    public function update(Object $object, ObjectDetail $detail, DetailFormRequest $request): RedirectResponse
    {
        $detail->update($request->validated());

        return $this->redirect($request, $detail);
    }
}
