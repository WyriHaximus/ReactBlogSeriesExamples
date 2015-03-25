<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$i = 0;
$loop->addPeriodicTimer(1, function() use (&$i) {
    echo ++$i, PHP_EOL;
});

$loop->run();
