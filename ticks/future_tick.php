<?php

require '../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();

$loop->addTimer(0.1, function () use ($loop) {
    $loop->stop();
});

function fooBar($loop) {
    return function () use ($loop) {
        echo 'a';
        $loop->futureTick(fooBar($loop));
    };
}

$loop->futureTick(fooBar($loop));
$loop->run();

echo PHP_EOL;

