<?php

namespace Aternos\Taskmaster\Communication\Promise;

use Aternos\Taskmaster\Communication\ResponseInterface;
use Exception;
use Throwable;

/**
 * Class ResponseDataPromise
 *
 * Promise implementation that wraps a {@link ResponsePromise} and resolves with the response data instead of the response itself.
 *
 * @package Aternos\Taskmaster\Communication\Promise
 */
class ResponseDataPromise extends Promise
{
    /**
     * @param ResponsePromise $responsePromise
     * @throws Throwable
     */
    public function __construct(protected ResponsePromise $responsePromise)
    {
        $this->responsePromise->then(function (ResponseInterface $response) {
            $this->resolve($response->getData());
        })->catch(function (Exception $exception) {
            $this->reject($exception);
        });
    }
}