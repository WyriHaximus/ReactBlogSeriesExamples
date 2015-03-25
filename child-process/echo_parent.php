<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$process = new React\ChildProcess\Process('php echo_child.php');

$loop->addTimer(0.001, function($timer) use ($process) {
    $loop = $timer->getLoop();

    $process->on('exit', function($output) use ($loop) {
        $loop->stop();
    });

    $process->start($loop);

    $process->stdout->on('data', function($output) {
        echo $output, PHP_EOL;
    });

    $i = 0;
    $loop->addPeriodicTimer(1, function ($timer) use (&$i, $process) {
        $process->stdin->write($i++);
    });
});

$loop->run();
