<?php

namespace YaTmch\Kick;

use Illuminate\Support\Facades\Validator;

class DataValidator
{
    private $rulesSuccess = [
        'success' => 'required|bool',
        'data.suggestions' => 'present|array',
        'data.suggestions.*.region' => 'required|string',
        'data.suggestions.*.value' => 'required|string',
        'data.suggestions.*.coordinates.geo_lat' => 'required|string',
        'data.suggestions.*.coordinates.geo_lon' => 'required|string',
    ];

    private $rulesFalse = [
        'success' => 'required|bool',
        'data' => 'present|array',
        'data.*.code' => 'required|int',
        'data.*.message' => 'required|string',
    ];

    public function validate(array $data)
    {
        $rules = (isset($data['success']) && $data['success'])
            ? $this->rulesSuccess
            : $this->rulesFalse;

        // TODO Убраль зависимость от фасада
        $validator = Validator::make($data, $rules);

        if (!$validator->fails()) {
            return [];
        }

        return collect($validator->errors()->keys())->map(function ($key) {
            // TODO Сформировать ошибку
            return "$key is invalid";
        });
    }
}

