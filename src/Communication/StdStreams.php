<?php

namespace Aternos\Taskmaster\Communication;

class StdStreams
{
    protected static ?self $instance = null;

    /**
     * @var resource|null
     */
    protected $stdin = null;

    /**
     * @var resource|null
     */
    protected $stdout = null;

    /**
     * @var resource|null
     */
    protected $stderr = null;

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return resource
     */
    public function getStdin()
    {
        if ($this->stdin === null) {
            if (defined("STDIN")) {
                $this->stdin = STDIN;
            } else {
                $this->stdin = fopen("php://stdin", "r");
            }
        }

        return $this->stdin;
    }

    /**
     * @return resource
     */
    public function getStdout()
    {
        if ($this->stdout === null) {
            if (defined("STDOUT")) {
                $this->stdout = STDOUT;
            } else {
                $this->stdout = fopen("php://stdout", "w");
            }
        }

        return $this->stdout;
    }

    /**
     * @return resource
     */
    public function getStderr()
    {
        if ($this->stderr === null) {
            if (defined("STDERR")) {
                $this->stderr = STDERR;
            } else {
                $this->stderr = fopen("php://stderr", "w");
            }
        }

        return $this->stderr;
    }
}
