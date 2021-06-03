<?php

namespace Warrior\Ticketer;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Warrior\Ticketer\Skeleton\SkeletonClass
 */
class TicketerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ticketer';
    }
}
