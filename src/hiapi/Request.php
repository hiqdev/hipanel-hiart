<?php
declare(strict_types=1);

namespace hipanel\hiart\hiapi;

class Request extends \hiqdev\hiart\guzzle\Request
{
    protected $responseClass = Response::class;

    public static function isSupported()
    {
        return true;
    }
}
