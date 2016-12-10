<?php

/**
 * JWTAuth
 *
 * Parith :: a compact PHP framework
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */
namespace Parith\Lib;

use Parith\Result;

class JWTAuth extends Result
{
    public $options = [
        'secret' => 'PLEASE_CHANGE_ME',
        'ttl' => 43200,
        'algo' => 'sha256',
    ];

    public $cookie, $token_key;

    public function __construct()
    {
        $this->setOptions(\Parith\App::getOption('jwtauth'));

        $this->cookie = new Cookie();
        $this->token_key = $this->cookie->options['token_key'];
    }

    /**
     * @param $id
     * @param array $payload
     * @return bool
     */
    public function setToken($id, array $payload)
    {
        return $this->cookie->set($this->token_key, $this->sign($id, $payload));
    }

    /**
     * @param bool $refresh
     * @return array
     */
    public function getToken($refresh = false)
    {
        $payload = $this->verify($this->cookie->get($this->token_key));
        if ($payload && $payload['exp'] < \APP_TS) {
            $payload = $refresh ? $this->refreshToken($payload) : [];
        }

        return $payload;
    }

    /**
     * @param $payload
     * @return array
     */
    public function refreshToken($payload)
    {
        $payload = $this->makePayload($payload['sub'], $payload);

        $this->setToken(0, $payload);

        return $payload;
    }

    /**
     * @param $id
     * @param array $payload
     * @return array
     */
    public function makePayload($id, array $payload)
    {
        return [
            'sub' => $id, //Subscriber
            'iss' => URI::base(), //Issuer
            'iat' => \APP_TS, //Issued At
            'exp' => \APP_TS + $this->options['ttl'], //Expiration
            'nbf' => \APP_TS, //Not Before
            'jti' => md5("jti.$id." . \APP_TS), //unique id
        ] + $payload;
    }

    /**
     * @param $id
     * @param array $payload
     * @return string
     */
    public function sign($id, array $payload)
    {
        $alg = $this->options['algo'];

        if ($id)
            $payload = $this->makePayload($id, $payload);

        $encoder = new Base64Encoder();

        $payload = $encoder->encode(json_encode($payload, \JSON_UNESCAPED_UNICODE));
        $header = $encoder->encode(json_encode(['typ' => 'JWT', 'alg' => $alg]));

        $data = "$header.$payload";

        $sign = hash_hmac($alg, $data, $this->options['secret'], true);

        return $data . '.' . $encoder->encode($sign);
    }

    /**
     * @param $data
     * @return array
     */
    public function verify($data)
    {
        $parts = explode('.', $data, 3);
        if (count($parts) != 3)
            return [];

        $encoder = new Base64Encoder();

        //$header = json_decode($encoder->decode($parts[0]), true);
        $sign = $encoder->decode($parts[2]);

        $sign_input = hash_hmac($this->options['algo'], "{$parts[0]}.{$parts[1]}", $this->options['secret'], true);

        if (hash_equals($sign, $sign_input)) {
            //return payload
            $payload = json_decode($encoder->decode($parts[1]), true);
            if ($payload['exp'] >= \APP_TS)
                return $payload;
        }

        return [];
    }

    /**
     * @param $signature
     * @param $signedInput
     * @return bool
     */
    public function timingSafeEquals($signature, $signedInput)
    {
        $signatureLength = strlen($signature);
        $signedInputLength = strlen($signedInput);
        $result = 0;

        if ($signedInputLength != $signatureLength) {
            return false;
        }

        for ($i = 0; $i < $signedInputLength; $i++) {
            $result |= (ord($signature[$i]) ^ ord($signedInput[$i]));
        }

        return $result === 0;
    }
}