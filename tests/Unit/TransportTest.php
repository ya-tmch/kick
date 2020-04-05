<?php

namespace YaTmch\Kick\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use YaTmch\Kick\Exceptions\InvalidJsonException;
use YaTmch\Kick\Exceptions\TransportException;
use YaTmch\Kick\Transport;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class TransportTest extends TestCase
{
    public $url = 'http://some.ru/some/path/to/json';
    public $timeout = 10;

    public function testCallUrl()
    {
        $container = [];
        $history = Middleware::history($container);

        $handlerStack = HandlerStack::create(new MockHandler([
            new Response(200, [], '[]')
        ]));

        $handlerStack->push($history);

        (new Transport(
            new Client(['handler' => $handlerStack])
        ))->get($this->url, $this->timeout);

        $this->assertCount(1, $container);
        $this->assertEquals($this->url, $container[0]['request']->getUri());
    }

    public function testSuccessRequest()
    {
        $result = [
            'some' => 'content'
        ];

        $client = $this->makeClient([
            new Response(200, [], json_encode($result)),
        ]);

        $response = (new Transport($client))->get($this->url, $this->timeout);

        $this->assertEquals($result, $response);
    }

    public function testBadHttpStatus()
    {
        $this->expectException(TransportException::class);

        $client = $this->makeClient([
            new Response(500, [], 'Some error'),
        ]);

        (new Transport($client))->get($this->url, $this->timeout);
    }

    public function testBadConnection()
    {
        $this->expectException(TransportException::class);

        $client = $this->makeClient([
            new RequestException('Error Communicating with Server', new Request('GET', 'test'))
        ]);

        (new Transport($client))->get($this->url, $this->timeout);
    }

    public function testInvalidJson()
    {
        $this->expectException(InvalidJsonException::class);

        $client = $this->makeClient([
            new Response(200, [], '{someString}'),
        ]);

        (new Transport($client))->get($this->url, $this->timeout);
    }

    private function makeClient($queue)
    {
        $handlerStack = HandlerStack::create(new MockHandler($queue));

        return new Client(['handler' => $handlerStack]);
    }
}
