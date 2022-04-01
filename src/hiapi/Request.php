<?php
declare(strict_types=1);

namespace hipanel\hiart\hiapi;

class Request extends \hiqdev\hiart\guzzle\Request
{
    protected $responseClass = Response::class;

    public function fill(string $method, string $path, array $queryParams = [], array $body = []): self
    {
        $this->isBuilt = null;
        $this->setDbname('hiapi')
            ->setMethod($method)
            ->setUri($path)
            ->setParams($queryParams)
            ->setBody($body)
            ->setAuth();

        return $this;
    }

    public static function isSupported()
    {
        return parent::isSupported();
    }

    public function setMethod(string $method): self
    {
        $this->method = strtoupper($method);

        return $this;
    }

    public function setDbname(string $dbname): self
    {
        $this->dbname = $dbname;

        return $this;
    }

    public function setUri(string $uri): self
    {
        $this->uri = $uri;

        return $this;
    }

    public function setAuth(): self
    {
        $auth = $this->builder->db->getAuth();
        if (isset($auth['access_token'])) {
            $this->headers['Authorization'] = 'Bearer ' . $auth['access_token'];
        }
        if (isset($auth['auth_ip'])) {
            $this->headers['X-User-Ip'] = $auth['auth_ip'];
        }

        return $this;
    }

    public function setParams(array $queryParams = []): self
    {
        if (is_array($queryParams)) {
            $params = http_build_query($queryParams);
        }
        if (!empty($params)) {
            $this->uri .= '?' . $params;
        }

        return $this;
    }

    public function setBody(array $body = []): self
    {
        if (!empty($body)) {
            $this->body = is_array($body) ? http_build_query($body, '', '&') : $body;
            $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        return $this;
    }

    protected function updateFromQuery()
    {
        $this->buildProtocolVersion();
    }
}
