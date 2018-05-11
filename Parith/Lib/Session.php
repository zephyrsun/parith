<?php

namespace Parith\Lib;

use Parith\Result;

class Session extends Result
{
    public function __construct()
    {
        session_start();
        $this->__ = &$_SESSION;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return session_id();
    }

    /**
     * @param $value
     * @return $this
     */
    public function setId($value)
    {
        session_id($value);

        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        session_write_close();

        return $this;
    }

    /**
     * @return $this
     */
    public function destroy()
    {
        session_unset();
        session_destroy();

        return $this;
    }

    /**
     * @param mixed $key
     * @return $this
     */
    public function delete($key)
    {
        parent::delete($key);

        return $this;
    }
}