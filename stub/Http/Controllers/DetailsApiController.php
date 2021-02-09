<?php

namespace TypiCMS\Modules\Objects\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use TypiCMS\Modules\Core\Filters\FilterOr;
use TypiCMS\Modules\Core\Http\Controllers\BaseApiController;
use TypiCMS\Modules\Objects\Models\ObjectDetail;
use TypiCMS\Modules\Objects\Models\Object;


class DetailsApiController extends BaseApiController
{
    public function index(Object $object, Request $request): LengthAwarePaginator
    {
        $data = QueryBuilder::for(ObjectDetail::class)
            ->selectFields($request->input('fields.object_details'))
            ->allowedSorts(['status_translated', 'title_translated', 'position'])
            ->allowedFilters([
                AllowedFilter::custom('title', new FilterOr()),
            ])
            ->allowedIncludes(['image'])
            ->where('object_id', $object->id)
            ->paginate($request->input('per_page'));

        return $data;
    }

    protected function updatePartial(Object $object, ObjectDetail $detail, Request $request)
    {
        foreach ($request->only('status') as $key => $content) {
            if ($detail->isTranslatableAttribute($key)) {
                foreach ($content as $lang => $value) {
                    $detail->setTranslation($key, $lang, $value);
                }
            } else {
                $detail->{$key} = $content;
            }
        }

        $detail->save();
    }

    public function destroy(Object $object, ObjectDetail $detail)
    {
        $detail->delete();
    }
}
