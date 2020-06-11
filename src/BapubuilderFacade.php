<?php

namespace Thecodebunny\Bapubuilder;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Thecodebunny\Bapubuilder\Skeleton\SkeletonClass
 */
class BapubuilderFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bapubuilder';
    }
}
