<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$timer = $loop->addTimer(1, function() {
    echo 'Timer done', PHP_EOL;
});

$loop->addTimer(3, function () use ($timer, $loop) {
    if ($loop->isTimerActive($timer)) {
        echo 'Timer active', PHP_EOL;
    } else {
        echo 'Timer inactive', PHP_EOL;
    }
});

$loop->run();
