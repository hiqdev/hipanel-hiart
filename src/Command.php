<?php
/**
 * HiPanel API client made with HiART.
 *
 * @link      https://github.com/hiqdev/hipanel-hiart
 * @package   hipanel-hiart
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017, HiQDev (http://hiqdev.com/)
 */

namespace hipanel\hiart;

use hiqdev\hiart\AbstractResponse;
use yii\helpers\Json;

/**
 * Command for HiPanel API.
 */
class Command extends \hiqdev\hiart\Command
{
    public function search($options = [])
    {
        /** @var AbstractResponse $response */
        $response = parent::search($options);

        if ($this->request->getQuery()->getOption('batch')) {
            return $response;
        }

        if ($response->getData() === []) {
            return $this->fakeResponseWithData($response, '');
        }

        return $this->fakeResponseWithData($response, Json::encode(reset($response->getData())));
    }

    private function fakeResponseWithData(AbstractResponse $response, $data)
    {
        $newResponse = clone $response;
        $newResponseReflection = new \ReflectionObject($newResponse);

        $isDecodedProperty = $newResponseReflection->getProperty('isDecoded');
        $isDecodedProperty->setAccessible(true);
        $isDecodedProperty->setValue($newResponse, false);
        $isDecodedProperty->setAccessible(false);

        $rawDataProperty = $newResponseReflection->getProperty('rawData');
        $rawDataProperty->setAccessible(true);
        $rawDataProperty->setValue($newResponse, $data);
        $rawDataProperty->setAccessible(false);

        return $newResponse;
    }
}
