<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

/**
 * Class WorkerDiedRequest
 *
 * Sent by the proxy runtime to a worker when the worker dies unexpectedly
 *
 * @package Aternos\Taskmaster\Communication\Request
 */
class WorkerDiedRequest extends Request
{
    /**
     * @param string|null $reason
     */
    public function __construct(protected ?string $reason = null)
    {
        parent::__construct();
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}