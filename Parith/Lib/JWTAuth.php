<?php
/**
 * Auth
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

class JWTAuth
{
    public $options = [
        'secret' => 'fFbGiq0acW9mJea7ZZuPwNQOP5u5TS2f',
        'ttl' => 86400,
        'refresh_ttl' => 41760,
        'algo' => 'sha256',
        'encoder' => Base64Encoder::class,
    ];

    public function __construct()
    {
        $this->options = \Parith\App::getOption('jwtauth') + $this->options;
    }

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

    public function sign(array $payload, $id = 0)
    {
        if ($id)
            $payload = $this->makePayload($id, $payload);

        $encoder = new $this->options['encoder'];
        $alg = $this->options['algo'];

        $payload = $encoder->encode(json_encode($payload, \JSON_UNESCAPED_UNICODE));
        $header = $encoder->encode(json_encode(['typ' => 'JWT', 'alg' => $alg]));

        $data = "$header.$payload";

        $sign = hash_hmac($alg, $data, $this->options['secret'], true);

        return $data . '.' . $encoder->encode($sign);
    }

    public function authenticate($token)
    {
        $parts = explode('.', $token, 3);
        if (count($parts) != 3)
            return false;

        $encoder = new $this->options['encoder'];

        //$header = json_decode($encoder->decode($parts[0]), true);
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

class Base64Encoder
{
    public function encode($data)
    {
        //return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        return strtr(base64_encode($data), '+/', '-_');
    }

    public function decode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}