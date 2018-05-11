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
        'algo' => 'sha256',
    ];

    public $cookie, $token_key, $ttl;

    public function __construct()
    {
        $this->setOptions(\Parith::getEnv('jwtauth'));
    }

    /**
     * @param array $payload
     * @param string $id
     * @return string
     */
    public function keyEncrypt(array $payload, $id = '')
    {
        return $this->sign($id, $payload);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function keyDecrypt($data)
    {
        $payload = $this->verify($data);
        if ($payload && $payload['exp'] < \APP_TS) {
            return false;
        }

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
                'iss' => URL::base(), //Issuer
                'iat' => \APP_TS, //Issued At
                'exp' => \APP_TS + $this->ttl, //Expiration
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
     * @return bool|mixed
     */
    public function verify($data)
    {
        $parts = explode('.', $data, 3);
        if (count($parts) != 3)
            return false;

        $encoder = new Base64Encoder();

        //$header = json_decode($this->encoder->decode($parts[0]), true);
        $sign = $encoder->decode($parts[2]);

        $sign_input = hash_hmac($this->options['algo'], "{$parts[0]}.{$parts[1]}", $this->options['secret'], true);

        if (hash_equals($sign, $sign_input)) {
            //return payload
            $payload = json_decode($encoder->decode($parts[1]), true);
            if ($payload['exp'] >= \APP_TS)
                return $payload;
        }

        return false;
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