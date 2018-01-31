<?php

use function React\Promise\reject;

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$kernel = \Recoil\React\ReactKernel::create($loop);

$kernel->execute(function () {
    try {
        yield reject(new Exception('error'));
    } catch (Throwable $et) {
        echo (string)$et;
    }
});

$loop->run();