<?php

namespace TypiCMS\Modules\Objects\Facades;

use Illuminate\Support\Facades\Facade;

class ObjectDetails extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ObjectDetails';
    }
}
