<?php declare(strict_types=1);

namespace hipanel\hiart\hiapi;

use hiqdev\hiart\ResponseInterface;

/**
 * $params contains query parameters; $body contains form fields.
 * $options contains per-request Guzzle options (e.g. json, timeout, headers).
 * Leave $body empty when supplying a body through $options.
 */
interface HiapiConnectionInterface
{
    public function get(string $path, array $params = [], array $options = []): ResponseInterface;

    public function head(string $path, array $params = [], array $options = []): ResponseInterface;

    public function post(string $path, array $params = [], array $body = [], array $options = []): ResponseInterface;

    public function put(string $path, array $params = [], array $body = [], array $options = []): ResponseInterface;

    public function delete(string $path, array $params = [], array $body = [], array $options = []): ResponseInterface;
}
