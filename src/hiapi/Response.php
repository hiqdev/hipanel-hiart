<?php
declare(strict_types=1);

namespace hipanel\hiart\hiapi;

final class Response extends \hiqdev\hiart\guzzle\Response
{
    public function getData()
    {
        return parent::getData()['data'] ?? $this->data;
    }
}
