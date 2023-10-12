<?php

namespace Aternos\Taskmaster\Communication\Request;

use Aternos\Taskmaster\Communication\Request;

class WorkerDiedRequest extends Request
{
    public function __construct(protected ?string $reason = null)
    {
        parent::__construct();
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }
}