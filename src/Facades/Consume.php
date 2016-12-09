<?php

namespace Teepluss\Consume\Facades;

use Illuminate\Support\Facades\Facade;

class Consume extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'consume.api'; }

}
