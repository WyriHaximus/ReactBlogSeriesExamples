<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$i = 0;
$timer = $loop->addPeriodicTimer(1, function() use (&$i) {
    echo ++$i, PHP_EOL;
});

$loop->addTimer(15, function () use ($timer, $loop) {
    $loop->cancelTimer($timer);
});

$loop->run();