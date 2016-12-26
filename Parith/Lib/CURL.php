<?php

/**
 * grab - CURL
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package   Parith
 * @author    Zephyr Sun
 * @copyright 2009-2016 Zephyr Sun
 * @license   http://www.parith.net/license
 * @link      http://www.parith.net/
 */

namespace Parith\Lib;

use Parith\Result;

class CURL extends Result
{
    public $error = '', $params = [];

    public $options = [
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
    ];

    public function __construct()
    {
        $this->setOptions(\Parith::env('curl'));
    }

    public function post($url, $data = [], array $options = [])
    {
        $options += [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
        ];

        return $this->exec($url, $options);
    }

    /**
     * get data like curl -d:
     * get('http://example.com', [], [\CURLOPT_POSTFIELDS => $string_data])
     *
     * @param $url
     * @param array $data
     * @param array $options
     * @return mixed
     */
    public function get($url, $data = [], array $options = [])
    {

        if ($data) {
            if (is_array($data))
                $data = http_build_query($data);

            $url .= '?' . $data;
        }
        /*
        $options += [
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => $data,
        ];
        */

        return $this->exec($url, $options);
    }

    public function put($url, $data = [], array $options = [])
    {
        $options += [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $data,
        ];

        return $this->exec($url, $options);
    }

    public function delete($url, array $options = [])
    {
        $options += [
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        ];

        return $this->exec($url, $options);
    }

    protected function exec($url, array $options = [])
    {
        $ch = curl_init();

        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $this->params = $options + $this->options);

        $result = curl_exec($ch);

        $this->error = curl_error($ch);

        curl_close($ch);

        return $result;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getParams()
    {
        return $this->params;
    }
}