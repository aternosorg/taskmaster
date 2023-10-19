# aternos/taskmaster

Taskmaster is an object-oriented PHP library for running tasks in parallel.

A task can be written in a few lines of code and then executed in different environments, e.g. in a
forked process, a new process, a thread or just synchronous in the same process without changing any code.

It's even possible to proxy the creation of the environment through a proxy process that uses a different
PHP binary/installation with different extensions. This allows using the advantages of the different
strategies even in environments where this would not be possible otherwise, e.g. using forked processes
on a webserver.

Tasks can communicate back to the main process during execution and handle results and errors gracefully.

## Installation

```bash
composer require aternos/taskmaster
```

To use the [`ForkWorker`](src/Environment/Fork/ForkWorker.php) you need to install
the [`pcntl`](https://www.php.net/manual/en/book.pcntl.php) extension.
For the [`ThreadWorker`](src/Environment/Thread/ThreadWorker.php)
the [`parallel`](https://www.php.net/manual/en/book.parallel.php) extension is required.

## Usage

### Basic Example

```php
// Every task is its own class, note that the class should be autoloaded
class SleepTask extends \Aternos\Taskmaster\Task\Task {

    // The run method is called when the task is executed
    public function run(): void 
    {
        sleep(1);
    }
}

// The taskmaster object holds tasks and workers
$taskmaster = new \Aternos\Taskmaster\Taskmaster();

// Set the bootstrap/autoloading file for new processes
// Taskmaster will try to automatically detect the correct composer autoloader if this is not set
$taskmaster->setBootstrap(__DIR__ . '/vendor/autoload.php');

// Set up the workers automatically
$taskmaster->autoDetectWorkers(4);

// Add the task to the taskmaster 8 times
$taskmaster->addTasks(new SleepTask(), 8);

// Wait for all tasks to finish and stop the taskmaster
$taskmaster->wait()->stop();
```

### Writing tasks

A task is a class, it is recommended to extend the [`Task`](src/Task/Task.php) class, but implementing
the [`TaskInterface`](src/Task/TaskInterface.php) is also possible.

A class must define a run function and has some optional functions such as error handlers.

Tasks are serialized and therefore must not contain any unserializable fields such as closures or
resources. They can define those fields when the task is executed in the run function.

#### `Task::run()`

The run function is called when the task is executed. It's the only required function for
a task. It can return a value that is passed back to the main process and can be handled by
defining a `Task::handleResult(mixed $result)` function in your task.

#### `Task::handleResult(mixed $result): void`

The `handleResult` function is called when the task returns a value. It can be used to handle
the result of the task. It's not required to define this function.

The first parameter is the result of the task or `null` if the task did not return a value.
The default implementation in the [`Task`](src/Task/Task.php) class just stores the result in
the task object for later access using the `Task::getResult()` function. If you override this
function, you should call the parent function to store the result or store the result yourself.

#### `Task::handleError(ErrorResponse $error): void`

The `handleError` function is called when the task caused a fatal unrecoverable error. There are
different kind of errors that can occur represented by
different [`ErrorResponse`](src/Communication/Response/ErrorResponse.php)
classes.

Common error responses are an [`ExceptionResponse`](src/Communication/Response/ExceptionResponse.php)
caused by an uncaught exception, a [`PhpFatalErrorResponse`](src/Communication/Response/PhpFatalErrorResponse.php)
caused by a fatal PHP error or a [`WorkerFailedResponse`](src/Communication/Response/WorkerFailedResponse.php) if
the worker process exited unexpectedly.

PHP fatal errors can only be caught if they were caused in a separate process, e.g. when using
the [ForkWorker](src/Environment/Fork/ForkWorker.php)
or the [`ProcessWorker`](src/Environment/Process/ProcessWorker.php). It's not recommended to rely on this.

If a worker fails and the worker gets a [`WorkerFailedResponse`](src/Communication/Response/WorkerFailedResponse.php),
it
is possible that this was not caused by the task itself and therefore a retry of the task might be possible.
This should be limited to a few retries to prevent endless loops.

The default error handler implementation in the [`Task`](src/Task/Task.php) class stores the error in
the task object for later access using the `Task::getError()` function and writes the error message to `STDERR`.
When overriding this function, you should call the parent function to store the error or store the error yourself.

#### `Task::handleUncriticalError(PhpError $error): bool`

The `handleUncriticalError` function is called when the task caused an uncritical error, e.g. a PHP warning.
The first parameter is a [`PhpError`](src/Communication/Response/PhpError.php) object that contains the error
details. The function should return `true` if the error was handled or `false` (default) if the PHP error
handler should continue (usually by logging/outputting the error).

The `handleUncriticalError` function is called in the same process as the task itself.

#### Call back to the main process

A task (usually) runs in a different process than the main process. The result and errors are communicated
back to the main process, but it's also possible to call back to the main process during execution.

The [`Task`](src/Task/Task.php) class provides the `Task::call()` and `Task::callAsync()` functions to call
a function in the main process. The `Task::call()` function blocks until the function is executed and the
result is returned. The `Task::callAsync()` function returns a [`Promise`](src/Communication/Promise/Promise.php)
that resolves when the function is executed and the result is returned. While it is theoretically possible
to send multiple requests to the main process at the same time, the main process still has to process them
synchronously and therefore the async calls have no benefit. It's only recommended to use the async calls
if you want to do something else in the task while waiting for the result.

The first parameter of the `Task::call()` and `Task::callAsync()` functions is a `Closure` of the function
that you want to call in the main process. The function has to be a public function of your task class. The second
and following parameters are the parameters that are passed to the function in the main process as first
and following arguments. The arguments have to be serializable. 

Example:

```php
class CallbackTask extends \Aternos\Taskmaster\Task\Task
{
    static protected int $current = 0;

    #[RunOnParent]
    public function getCurrent(): int
    {
        return static::$current++;
    }

    #[RunOnChild]
    public function run(): void
    {
        $current = $this->call($this->getCurrent(...));
        echo "I am task number $current\n";
    }
}
 ```

#### RunOn attributes

As seen in the example above, it's possible to define functions that are executed in the main process [`RunOnParent`](src/Task/RunOnParent.php),
in the child process [`RunOnChild`](src/Task/RunOnChild.php) or in both [`RunOnBoth`](src/Task/RunOnBoth.php).

These attributes are mostly used as a documentation for the developer to clearly show which methods are 
executed on which process. The only restriction is that functions marked with the [`RunOnChild`](src/Task/RunOnChild.php)
attribute must not be called using the `Task::call()` or `Task::callAsync()` functions.

#### Synchronized fields

A task is serialized at the beginning and sent to a [`Runtime`](src/Runtime/Runtime.php) to be executed.
After that, any changes to the task properties only happen in the runtime and might not be available
in the main process. To synchronize a field, it must be defined as a synchronized field using the
[`Synchronized`](src/Task/Synchronized.php) attribute.

Properties marked with this attribute are synchronized automatically when the task is executed, a remote call is
made using `Task::callAsync()` or `Task::call()` or when the task result is sent back to the parent.

The synchronisation ONLY happens on those events, changes to the property are not immediately synchronized.
The properties marked with this attribute have to be serializable.

