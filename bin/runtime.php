<?php

$bootstrap = $argv[1];
$runtimeClass = $argv[2];

require_once $bootstrap;
(new $runtimeClass())->start();