<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\ResponseInterface;
use Exception;

class ResponseDataPromise extends Promise
{
    public function __construct(protected ResponsePromise $responsePromise)
    {
        $this->responsePromise->then(function (ResponseInterface $response) {
            $this->resolve($response->getData());
        })->catch(function (Exception $exception) {
            $this->reject($exception);
        });
    }
}