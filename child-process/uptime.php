<?php

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$process = new React\ChildProcess\Process('uptime');

$loop->addTimer(0.001, function($timer) use ($process) {
    $process->start($timer->getLoop());

    $process->stdout->on('data', function($output) {
        echo $output;
    });
});

$loop->run();
