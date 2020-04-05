<?php

namespace YaTmch\Kick;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use YaTmch\Kick\Exceptions\InvalidJsonException;
use YaTmch\Kick\Exceptions\TransportException;

class Transport
{
    private $defaultGuzzleConfig;

    public function __construct(Client $client)
    {
        // Как-то не очень...
        $this->defaultGuzzleConfig = $client->getConfig();
    }

    public function get($baseUrl, $timeout): array
    {
        try {
            $response = $this->send(
                $timeout, new Psr7\Request('GET', $baseUrl)
            );
        } catch (RequestException $e) {
            throw new TransportException($e->getMessage(), $e->getCode(), $e);
        }

        $json = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            // TODO Сформировать месадж
            throw new TransportException();
        }

        if (is_null($json)) {
            // TODO Сформировать месадж
            throw new InvalidJsonException();
        }

        return $json;
    }

    private function send($timeOut, RequestInterface $request): ResponseInterface
    {
        $config = array_merge($this->defaultGuzzleConfig, ['timeout' => $timeOut, 'http_errors' => false]);

        return (new Client($config))->send($request);
    }
}

