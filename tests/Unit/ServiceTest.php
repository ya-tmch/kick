<?php

namespace YaTmch\Kick\Tests\Unit;

use Illuminate\Contracts\Config\Repository;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use YaTmch\Kick\DataValidator;
use YaTmch\Kick\Exceptions\DataWithErrorsException;
use YaTmch\Kick\Exceptions\InvalidDataFormatException;
use YaTmch\Kick\Exceptions\InvalidJsonException;
use YaTmch\Kick\Exceptions\TransportException;
use YaTmch\Kick\Service;
use YaTmch\Kick\Transport;

class ServiceTest extends TestCase
{
    public $baseUrl = 'http://some.url';
    public $timeout = 10;
    public $successTransportResult = ['success' => true, 'data' => ['someData']];
    public $failedTransportResult = ['success' => false, 'data' => ['someData']];

    private function mockConfig()
    {
        return Mockery::mock(Repository::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with('kick.base_url')
                ->andReturn($this->baseUrl);

            $mock
                ->shouldReceive('get')
                ->once()
                ->with('kick.request_timeout')
                ->andReturn($this->timeout);
        });
    }

    public function testSuccessReceive()
    {
        $transport = Mockery::mock(Transport::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with($this->baseUrl, $this->timeout)
                ->andReturn($this->successTransportResult);
        });

        $validator = Mockery::mock(DataValidator::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('validate')
                ->once()
                ->with($this->successTransportResult)
                ->andReturn([]);
        });

        $service = new Service(
            $transport, $validator, $this->mockConfig()
        );

        $result = $service->receive();

        $this->assertEquals(
            $this->successTransportResult['data'], $result
        );
    }

    public function testReceiveWithTransportException()
    {
        $this->expectException(TransportException::class);

        $transport = Mockery::mock(Transport::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with($this->baseUrl, $this->timeout)
                ->andThrow(new TransportException());
        });

        $validator = Mockery::mock(DataValidator::class);

        $service = new Service(
            $transport, $validator, $this->mockConfig()
        );

        $service->receive();
    }

    public function testReceiveWithInvalidJsonException()
    {
        $this->expectException(InvalidJsonException::class);

        $transport = Mockery::mock(Transport::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with($this->baseUrl, $this->timeout)
                ->andThrow(new InvalidJsonException());
        });

        $validator = Mockery::mock(DataValidator::class);

        $service = new Service(
            $transport, $validator, $this->mockConfig()
        );

        $service->receive();
    }

    public function testReceiveWithFormatException()
    {
        $this->expectException(InvalidDataFormatException::class);

        $transport = Mockery::mock(Transport::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with($this->baseUrl, $this->timeout)
                ->andReturn($this->successTransportResult);
        });

        $validator = Mockery::mock(DataValidator::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('validate')
                ->once()
                ->with($this->successTransportResult)
                ->andThrow(InvalidDataFormatException::class);
        });

        $service = new Service(
            $transport, $validator, $this->mockConfig()
        );

        $service->receive();
    }

    public function testReceiveWithDataError()
    {
        $this->expectException(DataWithErrorsException::class);

        $transport = Mockery::mock(Transport::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('get')
                ->once()
                ->with($this->baseUrl, $this->timeout)
                ->andReturn($this->failedTransportResult);
        });

        $validator = Mockery::mock(DataValidator::class, function (MockInterface $mock) {
            $mock
                ->shouldReceive('validate')
                ->once()
                ->with($this->failedTransportResult)
                ->andReturn([]);
        });

        $service = new Service(
            $transport, $validator, $this->mockConfig()
        );

        $service->receive();
    }
}
