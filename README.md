# aternos/taskmaster

Taskmaster is an object-oriented PHP library for running tasks in parallel.

A task can be written in a few lines of code and then executed in different environments, e.g. in a
forked process, a new process, a thread or just synchronous in the same process without changing any code.

It's even possible to proxy the creation of the environment through a proxy process that uses a different
PHP binary/installation with different extensions. This allows using the advantages of the different
strategies even in environments where this would not be possible otherwise, e.g. using forked processes
on a webserver.

Tasks can communicate back to the main process during execution and handle results and errors gracefully.

<!-- TOC -->
* [aternos/taskmaster](#aternostaskmaster)
  * [Installation](#installation)
  * [Basic Example](#basic-example)
  * [Writing tasks](#writing-tasks)
    * [The `run()` function](#the-run-function)
    * [Call back to the main process](#call-back-to-the-main-process)
    * [RunOn attributes](#runon-attributes)
    * [Synchronized fields](#synchronized-fields)
    * [Handling the result](#handling-the-result)
    * [Handling errors](#handling-errors)
      * [Critical errors](#critical-errors)
      * [Uncritical errors](#uncritical-errors)
  * [Creating tasks](#creating-tasks)
    * [Task factory](#task-factory)
  * [Defining workers](#defining-workers)
    * [Available workers](#available-workers)
    * [Creating workers](#creating-workers)
    * [Proxy workers](#proxy-workers)
    * [Defining workers manually](#defining-workers-manually)
    * [Defining workers automatically](#defining-workers-automatically)
    * [Defining workers using environment variables](#defining-workers-using-environment-variables)
  * [Running tasks](#running-tasks)
    * [Waiting for tasks to finish](#waiting-for-tasks-to-finish)
    * [Running the update loop manually](#running-the-update-loop-manually)
    * [Stopping the taskmaster](#stopping-the-taskmaster)
  * [Task/worker groups](#taskworker-groups)
    * [Groups in task factories](#groups-in-task-factories)
<!-- TOC -->

## Installation

```bash
composer require aternos/taskmaster
```

To use the [`ForkWorker`](src/Environment/Fork/ForkWorker.php) you have to install
the [`pcntl`](https://www.php.net/manual/en/book.pcntl.php) extension.
For the [`ThreadWorker`](src/Environment/Thread/ThreadWorker.php)
the [`parallel`](https://www.php.net/manual/en/book.parallel.php) extension is required.

## Basic Example

```php
// Every task is its own class, the class should be autoloaded
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

// Add tasks to the taskmaster
for ($i = 0; $i < 8; $i++) {
    $taskmaster->addTask(new SleepTask());
}

// Wait for all tasks to finish and stop the taskmaster
$taskmaster->wait()->stop();
```

## Writing tasks

A task is a class, it is recommended to extend the [`Task`](src/Task/Task.php) class, but implementing
the [`TaskInterface`](src/Task/TaskInterface.php) is also possible.

A class must define a run function and has some optional functions such as error handlers.

Tasks are serialized and therefore must not contain any unserializable fields such as closures or
resources. They can define those fields when the task is executed in the run function.

### The `run()` function

The run function is called when the task is executed. It's the only required function for
a task. It can return a value that is passed back to the main process and can be handled by
defining a `Task::handleResult(mixed $result)` function in your task.

### Call back to the main process

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

### RunOn attributes

As seen in the example above, it's possible to define functions that are executed in the main
process [`RunOnParent`](src/Task/RunOnParent.php),
in the child process [`RunOnChild`](src/Task/RunOnChild.php) or in both [`RunOnBoth`](src/Task/RunOnBoth.php) using
attributes.

These attributes are optional and mostly used as a documentation for the developer to clearly show which methods are
executed where. The only implemented restriction is that functions marked with
the [`RunOnChild`](src/Task/RunOnChild.php)
attribute must not be called using the `Task::call()` or `Task::callAsync()` functions.

### Synchronized fields

A task is serialized at the beginning and sent to a [`Runtime`](src/Runtime/Runtime.php) to be executed.
After that, any changes to the task properties only happen in the runtime and might not be available
in the main process. To synchronize a field, it must be defined as a synchronized field using the
[`Synchronized`](src/Task/Synchronized.php) attribute.

Properties marked with this attribute are synchronized automatically when the task is executed, a remote call is
made using `Task::callAsync()` or `Task::call()` or when the task result is sent back to the parent.

The synchronisation ONLY happens on those events, changes to the property are not immediately synchronized.
The properties marked with this attribute have to be serializable.

Example:

```php
class SynchronizedFieldTask extends \Aternos\Taskmaster\Task\Task
{
    #[Synchronized] 
    protected int $counter = 0;

    #[RunOnBoth]
    public function increaseCounter(): void
    {
        $this->counter++;
    }

    #[RunOnChild]
    public function run(): null
    {
        for ($i = 0; $i < 3; $i++) {
            $this->increaseCounter();
            $this->call($this->increaseCounter(...));
        }
        return $this->counter;
    }
}
```

The result of this task is `6` because the `counter` property is synchronized and increased on both sides.

### Handling the result

The `handleResult` function is called when the task returns a value. It can be used to handle
the result of the task. It's not required to define this function.

The first parameter is the result of the task or `null` if the task did not return a value.
The default implementation in the [`Task`](src/Task/Task.php) class just stores the result in
the task object for later access using the `Task::getResult()` function. If you override this
function, you should call the parent function to store the result or store the result yourself.

### Handling errors

#### Critical errors

The `handleError` function is called when the task caused a fatal unrecoverable error. There are
different kind of errors that can occur represented by
different [`ErrorResponse`](src/Communication/Response/ErrorResponse.php)
classes.

Common error responses are an [`ExceptionResponse`](src/Communication/Response/ExceptionResponse.php)
caused by an uncaught exception, a [`PhpFatalErrorResponse`](src/Communication/Response/PhpFatalErrorResponse.php)
caused by a fatal PHP error or a [`WorkerFailedResponse`](src/Communication/Response/WorkerFailedResponse.php) if
the worker process exited unexpectedly.

PHP fatal errors can only be caught if they were caused in a separate process, e.g. when using
the [`ForkWorker`](src/Environment/Fork/ForkWorker.php)
or the [`ProcessWorker`](src/Environment/Process/ProcessWorker.php). It's not recommended to rely on this.

If a worker fails and the worker gets a [`WorkerFailedResponse`](src/Communication/Response/WorkerFailedResponse.php),
it
is possible that this was not caused by the task itself and therefore a retry of the task might be possible.
This should be limited to a few retries to prevent endless loops.

The default error handler implementation in the [`Task`](src/Task/Task.php) class stores the error in
the task object for later access using the `Task::getError()` function and writes the error message to `STDERR`.
When overriding this function, you should call the parent function to store the error or store the error yourself.

#### Uncritical errors

The `handleUncriticalError` function is called when the task caused an uncritical error, e.g. a PHP warning.
The first parameter is a [`PhpError`](src/Communication/Response/PhpError.php) object that contains the error
details. The function should return `true` if the error was handled or `false` (default) if the PHP error
handler should continue (usually by logging/outputting the error).

The `handleUncriticalError` function is called in the same process as the task itself.

When executing tasks synchronously using the [`SyncWorker`](src/Environment/Sync/SyncWorker.php), no PHP
error handler is defined to avoid conflicts with other error handler of the main process. Therefore, the
`handleUncriticalError` function is not called in this case.

## Creating tasks

A task object can simply be created by instancing the task class:

```php
$task = new SleepTask();
```

And then added to the taskmaster:

```php
$taskmaster->addTask($task);
```

You can add all your tasks at the beginning:

```php
for ($i = 0; $i < 100; $i++) {
    $taskmaster->addTask(new SleepTask());
}
```

or wait for the taskmaster to finish some tasks and then add more to avoid holding all tasks in memory:

```php
for ($i = 0; $i < 10; $i++) {
    for ($j = 0; $j < 10; $j++) {
        $taskmaster->addTask(new SleepTask());
    }
    $taskmaster->waitUntilAllTasksAreAssigned();
}
```

### Task factory

The best way to dynamically create tasks when necessary is by creating a task factory, by extending the
[`TaskFactory`](src/Task/TaskFactory.php) class or implementing
the [`TaskFactoryInterface`](src/Task/TaskFactoryInterface.php).

```php
// Your own task factory extending the TaskFactory class
class SleepTaskFactory extends \Aternos\Taskmaster\Task\TaskFactory
{
    protected int $count = 0;

    public function createNextTask(?string $group): ?\Aternos\Taskmaster\Task\TaskInterface
    {
        if ($this->count++ < 100) {
            return new SleepTask();
        }
        
        // Stop creating tasks after 100 tasks
        return null;
    }
}

$taskmaster->addTaskFactory(new SleepTaskFactory());
```

You can also use the existing [`IteratorTaskFactory`](src/Task/IteratorTaskFactory.php) that creates tasks from an
iterator.

```php
// Create an iterator that iterates over all files in the current directory
$iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator("."));

// Create the task factory using the iterator and a task class that gets the iterator value as constructor argument
// Note, that the SplFileInfo object that you get from a DirectoryIterator is not serializable and therefore cannot be 
// stored in a task property, but you can use any other values, e.g. the file path
$factory = new \Aternos\Taskmaster\Task\IteratorTaskFactory($iterator, FileTask::class);

$taskmaster->addTaskFactory($factory);
```

You can add multiple task factories to a taskmaster. The taskmaster will use the factories in the order they were
added. If a factory returns `null`, the next factory is used.

## Defining workers

A worker executes tasks. There are different workers available that execute tasks in different environments.

### Available workers

Currently, the following workers are available:

| **Worker**                                                   | **Requirements**                                                                           | Notes                                                                                                 |
|--------------------------------------------------------------|--------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------|
| [`SyncWorker`](src/Environment/Sync/SyncWorker.php)          | None                                                                                       | The sync worker can be used as a fallback or if the number of tasks don't justify async execution.    |
| [`ProcessWorker`](src/Environment/Process/ProcessWorker.php) | `proc_open()` - This function is part of the PHP core but might be disabled.               | The process worker spawns an entirely new process which causes a slight overhead.                     |
| [`ForkWorker`](src/Environment/Fork/ForkWorker.php)          | `pcntl` extension - This extension can only be installed in CLI environments.              | Forking the current process is more lightweight than spawning a new process.                          |
| [`ThreadWorker`](src/Environment/Thread/ThreadWorker.php)    | `parallel` extension - Also requires a build of PHP with ZTS (Zend Thread Safety) enabled. | This worker is considered experimental and potentially unstable. It should not be used in production. |

You can also write your own worker by extending the existing workers or implementing
the [`WorkerInterface`](src/Worker/WorkerInterface.php).

### Creating workers

A worker object can simply be created by instancing the worker class:

```php
$worker = new \Aternos\Taskmaster\Environment\Sync\SyncWorker();
```

You can define custom options for the worker by creating a [`TaskmasterOptions`](src/TaskmasterOptions.php)
object and passing it to the worker. If the options are not set, the default options from your `Taskmaster`
instance are used.

```php
$options = new \Aternos\Taskmaster\TaskmasterOptions();
$options->setBootstrap(__DIR__ . '/vendor/autoload.php');
$options->setPhpExecutable('/usr/bin/php');

$worker = new \Aternos\Taskmaster\Environment\Process\ProcessWorker();
$worker->setOptions($options);
```

Currently only the bootstrap file and the PHP executable can be set as options. Those options are only
relevant for some workers, especially the [`ProcessWorker`](src/Environment/Process/ProcessWorker.php).

### Proxy workers

It's possible to proxy the creation of the worker through a proxy process that uses a different PHP binary or
environment, e.g. you can use a PHP CLI proxy process in a webserver environment to use
the [`ForkWorker`](src/Environment/Fork/ForkWorker.php).
One proxy can be used for multiple workers.

Currently, the only available proxy is the [`ProcessProxy`](src/Proxy/ProcessProxy.php) that uses a new process opened
using `proc_open()` to run the worker.

To use a proxy, create a new proxy object and pass it to the worker:

```php
$proxy = new \Aternos\Taskmaster\Proxy\ProcessProxy();
$worker = new \Aternos\Taskmaster\Environment\Fork\ForkWorker();
$worker->setProxy($proxy);
```

You can also define [`TaskmasterOptions`](src/TaskmasterOptions.php) for the proxy process.
If the options are not set, the default options from your `Taskmaster` instance are used.

```php
$options = new \Aternos\Taskmaster\TaskmasterOptions();
$options->setBootstrap(__DIR__ . '/vendor/autoload.php');
$options->setPhpExecutable('/usr/bin/php');

$proxy = new \Aternos\Taskmaster\Proxy\ProcessProxy();
$proxy->setOptions($options);
```

### Defining workers manually

Before running any tasks, you have to define the workers that should be used.

```php
// Add a single worker
$taskmaster->addWorker(new \Aternos\Taskmaster\Environment\Sync\SyncWorker());

// Add a worker multiple times
$taskmaster->addWorkers(new \Aternos\Taskmaster\Environment\Process\ProcessWorker(), 4);

// Add multiple workers with the same proxy
$worker = new \Aternos\Taskmaster\Environment\Fork\ForkWorker();
$worker->setProxy(new \Aternos\Taskmaster\Proxy\ProcessProxy());
$taskmaster->addWorkers($worker, 8);

// Define/replace all workers at once
$taskmaster->setWorkers([
    new \Aternos\Taskmaster\Environment\Fork\ForkWorker(),
    new \Aternos\Taskmaster\Environment\Fork\ForkWorker(),
    new \Aternos\Taskmaster\Environment\Fork\ForkWorker(),
]);
```

### Defining workers automatically

It's possible to detect the available workers and set them up automatically:

```php
// create 4 automatically detected workers
$taskmaster->autoDetectWorkers(4);
```

This will use the [`ForkWorker`](src/Environment/Fork/ForkWorker.php) if the `pcntl` extension is available,
or the [`ProcessWorker`](src/Environment/Process/ProcessWorker.php) if the `proc_open()` function is available
and fall back to the [`SyncWorker`](src/Environment/Sync/SyncWorker.php) otherwise.

The `autoDetectWorkers()` function also supports loading the worker configuration from environment variables.

### Defining workers using environment variables

When the `autoDetectWorkers()` function is called, it also checks the following environment variables
to create the worker configuration:

| Environment variable          | Description                                                                                                                                                                |
|-------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `TASKMASTER_WORKER_COUNT`     | The number of workers                                                                                                                                                      |
| `TASKMASTER_WORKER`           | The type of worker, currently available workers are `sync`, `fork`, `process` and `thread`. The requirements for these workers have to be met, they are not checked again. |
| `TASKMASTER_WORKER_PROXY`     | The type of proxy (if any) to use for the workers, currently only `process` is available.                                                                                  |
| `TASKMASTER_WORKER_BIN`       | Path to the PHP binary to use for the workers, currently only applies to `process` workers.                                                                                |
| `TASKMASTER_WORKER_PROXY_BIN` | Path to the PHP binary to use for the proxy.                                                                                                                               |

When using the `autoDetectWorkers()` function, it's possible to disable loading the worker configuration from
environment variables
by setting the second argument to `false` or to just disable loading the worker count by setting the third argument
to `false`.

```php
// Load count and workers from environment variables
$taskmaster->autoDetectWorkers(4);

// Load nothing from environment variables
$taskmaster->autoDetectWorkers(4, false);

// Load worker types from environment variables, but keep the worker count
$taskmaster->autoDetectWorkers(4, true, false);
```

## Running tasks

After writing your tasks, creating them and defining the workers, you can start running the tasks.
You don't have to explicitly start the taskmaster, just running the update loop through the wait
functions or manually is enough. Workers and proxies are started when necessary.

### Waiting for tasks to finish

You can simply wait for all tasks to finish using the `Taskmaster::wait()` function:

```php
$taskmaster->wait();
```

This function blocks until all tasks are finished and then stops the taskmaster.
If you might want to add further tasks, you can also use the `Taskmaster::waitUntilAllTasksAreAssigned()` function
to wait until all tasks are assigned to a worker and then add more tasks.

```php
$taskmaster->waitUntilAllTasksAreAssigned();
```

This doesn't wait for all tasks to finish, but when all tasks are assigned to a worker, it's the best
time to add more tasks to avoid any workers being idle.

You should still wait for all tasks to finish using `Taskmaster::wait()` before stopping the taskmaster.

### Running the update loop manually

You can also run the update loop manually and do something else between the updates:

```php
do {
    $taskmaster->update();
    // do something else
} while ($taskmaster->isRunning());
```
This is exactly the code of the `Taskmaster::wait()` function, but you can do something else between the updates.

### Stopping the taskmaster

After you've waited for all tasks to finish, you should stop the taskmaster:

```php
$taskmaster->stop();
```

## Task/worker groups

For a more complex setup, you can group several workers together and then define tasks that only
run on a certain group.

```php
// create a group A with 4 fork workers
$workerA = new \Aternos\Taskmaster\Environment\Fork\ForkWorker();
$workerA->setGroup('A');
$taskmaster->addWorkers($workerA, 4);

// create a group B with 2 process workers
$workerB = new \Aternos\Taskmaster\Environment\Process\ProcessWorker();
$workerB->setGroup('B');
$taskmaster->addWorkers($workerB, 2);

// create tasks that only run on group A
for ($i = 0; $i < 10; $i++) {
    $taskA = new SleepTask();
    $taskA->setGroup('A');
    $taskmaster->addTask($taskA);
}

// create tasks that only run on group B
for ($i = 0; $i < 5; $i++) {
    $taskB = new FileTask();
    $taskB->setGroup('B');
    $taskmaster->addTask($taskB);
}
```

### Groups in task factories

Task factories also support groups in two ways. 

You can directly define, for which groups the task factory
should be called by returning an array of groups from the `TaskFactory::getGroups()` function. You can return
`null` if you want to create tasks for all groups or `[null]` in an array if you want to be called for tasks
without a group.

And you get the group as a parameter in the `TaskFactory::createNextTask(?string $group)` function. The group
parameter is `null` if the task factory is called for tasks without a group.

```php
class SleepTaskFactory extends \Aternos\Taskmaster\Task\TaskFactory
{
    protected int $count = 0;
    
    public function getGroups() : ?array
    {
        return ['A', 'B'];
    }

    public function createNextTask(?string $group): ?\Aternos\Taskmaster\Task\TaskInterface
    {
        if ($this->count++ < 100) {
            return new SleepTask();
        }
        return null;
    }
}
```