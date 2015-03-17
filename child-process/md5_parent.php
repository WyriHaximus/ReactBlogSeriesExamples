<?php

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$process = new React\ChildProcess\Process('php md5_child.php');

$loop->addTimer(0.001, function($timer) use ($process) {
    $loop = $timer->getLoop();

    $process->on('exit', function($output) use ($loop) {
        $loop->stop();
    });

    $process->start($loop);

    $process->stdout->on('data', function($output) {
        echo $output;
    });

    for ($i = 0; $i < 16; $i++) {
        $process->stdin->write($i . PHP_EOL);
    }
});

$loop->run();
