<?php

/**
 * grab - CURL
 *
 * Parith :: a compact PHP framework
 * http://www.parith.net/
 *
 * @package Parith
 * @author Zephyr Sun
 * @copyright 2009-2013 Zephyr Sun
 * @license http://www.parith.net/license
 * @link http://www.parith.net/
 */

namespace Parith\Lib;

use \Parith\App;

class Grab
{
    public $url, $error, $params;

    public $options = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 2,
        CURLOPT_CONNECTTIMEOUT => 30,
    );

    public function __construct(array $options = array())
    {
        $this->options = $options + App::getOption('curl') + $this->options;
    }

    public function post($url, $args = array(), array $options = array())
    {
        return $this->exec($url, 'post', $args, $options);
    }

    public function get($url, $args = array(), array $options = array())
    {
        return $this->exec($url, 'get', $args, $options);
    }

    public function exec($url, $method = 'get', $args = array(), array $options = array())
    {
        $ch = curl_init();

        $options += $this->options;

        if ($method == 'post') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $args;
        } elseif ($method = 'get' && $args) {
            if (is_array($args))
                $args = http_build_query($args);

            $url .= '?' . $args;
        }

        $options[CURLOPT_URL] = $url;

        curl_setopt_array($ch, $this->params = $options);

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