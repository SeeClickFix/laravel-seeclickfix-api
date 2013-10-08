<?php namespace SeeClickFix\API\Facade;

use Illuminate\Support\Facades\Facade;

class API extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'SeeClickFix'; }

}