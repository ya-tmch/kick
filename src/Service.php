<?php

namespace YaTmch\Kick;

use Illuminate\Contracts\Config\Repository;
use YaTmch\Kick\Exceptions\DataWithErrorsException;
use YaTmch\Kick\Exceptions\InvalidDataFormatException;

class Service implements Contracts\Service
{
    /**
     * @var Transport
     */
    private $transport;

    /**
     * @var DataValidator
     */
    private $validator;

    /**
     * @var Repository
     */
    private $config;


    public function __construct(Transport $transport, DataValidator $validator, Repository $config)
    {
        $this->transport = $transport;
        $this->validator = $validator;
        $this->config = $config;
    }

    public function receive(): array
    {
        // Про логирование не понял.

        $result = $this->transport->get(
            $this->config->get('kick.base_url'),
            $this->config->get('kick.request_timeout')
        );

        if ($errors = $this->validator->validate($result)) {
            // TODO Сформировать месадж
            throw new InvalidDataFormatException($errors);
        }

        if (!$result['success']) {
            // По условиям из ТЗ кидаем ексепшен если не success
            // TODO Сформировать месадж из $result['data']
            throw new DataWithErrorsException();
        }

        return $result['data'];
    }
}

