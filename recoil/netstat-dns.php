<?php

use React\Dns\Resolver\Factory;

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$kernel = \Recoil\React\ReactKernel::create($loop);

$kernel->execute(function () use ($loop) {
    $listeningCount = yield \WyriHaximus\React\childProcessPromise($loop, new \React\ChildProcess\Process('netstat -tulpen | wc -l'));
    echo 'Listening Sockets: ', $listeningCount->getStdout(), PHP_EOL;
    $connectionCount = yield \WyriHaximus\React\childProcessPromise($loop, new \React\ChildProcess\Process('netstat -tupen | grep ESTABLISHED | wc -l'));
    echo 'Open Connections: ', $connectionCount->getStdout(), PHP_EOL;
});

$kernel->execute(function () use ($loop, $argv) {
    $resolver = (new Factory())->create('8.8.8.8', $loop);
    for ($i = 1; $i < count($argv); $i++) {
        $ip = yield $resolver->resolve($argv[$i]);
        echo $argv[$i], ': ', $ip, PHP_EOL;
    }
});

$loop->run();