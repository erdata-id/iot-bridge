<?php 

namespace Erdata\IotBridge;

use Illuminate\Http\Request;

class IotBridge
{
    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function unwrap(Request $request) {
        if (!check_config()) {
            return $this->respondWithMessage(false, 'No config found.');
        }

        $app_id = $this->config['app_id'] ?? config('iotbridge.app_id');
        $app_secret = $this->config['app_secret'] ?? config('iotbridge.app_secret');

        if (!$request->hasHeader('X-Endpoint-Id')) {
            return $this->respondWithMessage(false, 'Invalid request');
        }

        $endpoint_id = $request->header('X-Endpoint-Id');

        if (!$request->filled('ts') || !$request->filled('data')) {
            return $this->respondWithMessage(false, 'Invalid data format');
        }

        if ($endpoint_id != $app_id) {
            return $this->respondWithMessage(false, 'Invalid API endpoint ID');
        }

        $plainText = $this->decrypt($request->input('data'), $app_secret . $app_id);

        if (is_null($plainText)) {
            return $this->respondWithMessage(false, 'Error decrypting data');
        }

        return $this->respondWithData(true, $plainText);
    }

    public function respondWithData($status, $data) {
        return [
                'success' => $status,
                'data' => $data,
        ];
    }

    public function respondWithMessage($status, $message) {
        return [
                'success' => $status,
                'message' => $message,
        ];
    }

    /**
     * AES256.php
     * This file is part of AES-everywhere project (https://github.com/mervick/aes-everywhere)
     *
     * This is an implementation of the AES algorithm, specifically CBC mode,
     * with 256 bits key length and PKCS7 padding.
     *
     * Copyright Andrey Izman (c) 2018-2019 <izmanw@gmail.com>
     * Licensed under the MIT license
     *
     */
    public function decrypt($encrypted, $secret)
    {
        $encrypted = base64_decode($encrypted);
        $salted = substr($encrypted, 0, 8) == 'Salted__';

        if (!$salted) {
            return null;
        }

        $salt = substr($encrypted, 8, 8);
        $encrypted = substr($encrypted, 16);

        $salted = $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx . $secret . $salt, true);
            $salted .= $dx;
        }

        $key = substr($salted, 0, 32);
        $iv = substr($salted, 32, 16);

        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, true, $iv);
    }
}