<?php

namespace YaTmch\Kick\Tests\Unit;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use YaTmch\Kick\DataValidator;

class ValidatorTest extends TestCase
{
    // Для валидатора
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function testValidationWithoutErrors()
    {
        $validator = new DataValidator();

        $this->assertEmpty(
            $validator->validate([
                'success' => true,
                'data' => [
                    'suggestions' => []
                ]
            ])
        );

        $this->assertEmpty(
            $validator->validate([
                'success' => true,
                'data' => [
                    'suggestions' => [
                        [
                            'region' => 'Москва',
                            'value' => 'г Москва, ул Лубянка Б., д 12',
                            'coordinates' => [
                                'geo_lat' => '55.7618518',
                                'geo_lon' => '37.6284306',
                            ],
                        ]
                    ]
                ]
            ])
        );

        $this->assertEmpty(
            $validator->validate([
                'success' => false,
                'data' => []
            ])
        );

        $this->assertEmpty(
            $validator->validate([
                'success' => false,
                'data' => [
                    [
                        'code' => 2010,
                        'message' => 'Some error',
                    ]
                ]
            ])
        );
    }

    public function testValidationWithErrors()
    {
        $validator = new DataValidator();

        $this->assertNotEmpty(
            $validator->validate([])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => true
            ])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => true,
                'data' => []
            ])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => true,
                'data' => [
                    'suggestions' => [
                        [
                            'region' => 'Москва',
                        ]
                    ]
                ]
            ])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => true,
                'data' => [
                    'suggestions' => 'is string'
                ]
            ])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => false,
            ])
        );

        $this->assertNotEmpty(
            $validator->validate([
                'success' => false,
                'data' => 'is string'
            ])
        );
    }
}
