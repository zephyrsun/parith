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

use \Parith\App;

class CURL
{
    public $url, $error, $params;

    public $options = array(
        CURLOPT_HEADER => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
    );

    public function __construct(array $options = array())
    {
        $this->options = $options + App::getOption('curl') + $this->options;
    }

    public function post($url, $args = array(), array $options = array())
    {
        $options += array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $args,
        );

        return $this->exec($url, $options);
    }

    public function get($url, $args = array(), array $options = array())
    {
        if ($args) {
            if (is_array($args))
                $args = http_build_query($args);

            $url .= '?' . $args;
        }

        return $this->exec($url, $options);
    }

    public function put($url, $args = array(), array $options = array())
    {
        $options += array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $args,
        );

        return $this->exec($url, $options);
    }

    public function delete($url, array $options = array())
    {
        $options += array(
            CURLOPT_CUSTOMREQUEST => 'DELETE',
        );

        return $this->exec($url, $options);
    }

    protected function exec($url, array $options = array())
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