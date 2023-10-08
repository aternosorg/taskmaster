<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\ResponseInterface;

class ResponseDataPromise extends Promise
{
    public function __construct(protected ResponsePromise $responsePromise)
    {
        $this->responsePromise->then(function (ResponseInterface $response) {
            $this->resolve($response->getData());
        });
    }
}