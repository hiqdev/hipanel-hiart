<?php

namespace hipanel\hiart;

interface ConnectionInterface
{
    /**
     * Creates API command.
     * @param array $config
     * @return mixed response
     */
    public function createCommand(array $config = []);
}
