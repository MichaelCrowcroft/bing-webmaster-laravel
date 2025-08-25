<?php

namespace MichaelCrowcroft\BingWebmaster\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MichaelCrowcroft\BingWebmaster\BingWebmaster
 */
class BingWebmaster extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MichaelCrowcroft\BingWebmaster\BingWebmaster::class;
    }
}
