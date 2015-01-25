<?php

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$i = 0;
$loop->addPeriodicTimer(1, function(React\EventLoop\Timer\Timer $timer) use (&$i, $loop) {
    echo ++$i, PHP_EOL;

    if ($i >= 15) {
        $loop->cancelTimer($timer);
    }
});

$loop->run();
