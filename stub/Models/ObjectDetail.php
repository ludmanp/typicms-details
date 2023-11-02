<?php

namespace TypiCMS\Modules\Objects\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laracasts\Presenter\PresentableTrait;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;
use TypiCMS\Modules\Core\Models\Base;
use TypiCMS\Modules\Core\Models\File;
use TypiCMS\Modules\Core\Traits\HasFiles;
use TypiCMS\Modules\Core\Traits\Historable;
use TypiCMS\Modules\Objects\Presenters\DetailModulePresenter;

/**
 * Class Detail
 * @package TypiCMS\Modules\Objects\Models
 *
 * @property string title
 * @property bool status
 * @property string slug
 * @property string summary
 * @property string body
 * @property integer object_id
 * @property Object object
 */
class ObjectDetail extends Base implements Sortable
{
    use HasFiles;
    use HasTranslations;
    use Historable;
    use PresentableTrait;
    use SortableTrait;

    protected $presenter = DetailModulePresenter::class;

    protected $guarded = [];

    public $translatable = [
        'title',
        'slug',
        'status',
        'summary',
        'body',
    ];

    public function getThumbAttribute(): string
    {
        return $this->present()->image(null, 54);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(File::class, 'image_id');
    }

    public function previewUri(): string
    {
        $uri = '/';
        if ($this->id) {
            $uri = $this->uri();
        }

        return url($uri);
    }

    public function uri($locale = null): string
    {
        $locale = $locale ?: config('app.locale');
        $route = $locale.'::object-detail';
        if (Route::has($route)) {
            return route($route, [
                'slug'=> $this->object->translate('slug', $locale),
                'detailSlug' => $this->translate('slug', $locale)
            ]);
        }

        return '/';
    }

    public function buildSortQuery()
    {
        return static::where('object_id', $this->object_id);
    }

    public function object(): BelongsTo
    {
        return $this->belongsTo(Object::class, 'object_id');
    }

    public function editUrl(): string
    {
        $route = 'admin::edit-object_detail';
        if (Route::has($route)) {
            return route($route, ['object' => $this->object_id, 'detail' => $this->id]);
        }

        return route('admin::dashboard');
    }

    public function indexUrl(): string
    {
        $route = 'admin::edit-object';
        if (Route::has($route)) {
            return route($route, $this->object_id);
        }

        return route('admin::dashboard');
    }


}
