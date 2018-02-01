<?php

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$kernel = \Recoil\React\ReactKernel::create($loop);

$kernel->execute(function () use ($loop) {
    $listeningCount = yield \WyriHaximus\React\childProcessPromise($loop, new \React\ChildProcess\Process('netstat -tulpen | wc -l'));
    echo 'Listening Sockets: ', $listeningCount->getStdout(), PHP_EOL;
    $connectionCount = yield \WyriHaximus\React\childProcessPromise($loop, new \React\ChildProcess\Process('netstat -tupen | grep ESTABLISHED | wc -l'));
    echo 'Open Connections: ', $connectionCount->getStdout(), PHP_EOL;
});

$loop->run();
