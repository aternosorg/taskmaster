<?php

require_once __DIR__ . "/../vendor/autoload.php";

(new \Aternos\Taskmaster\Environment\Process\ProcessRuntime(
    fopen("php://fd/3", ""),
    fopen("php://fd/4", ""),
))->start();