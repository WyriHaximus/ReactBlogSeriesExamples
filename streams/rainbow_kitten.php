<?php

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$superFab = new \Fab\SuperFab();

$read = new \React\Stream\Stream(fopen('php://stdin', 'r+'), $loop);
$write = new \React\Stream\Stream(fopen('php://stdout', 'w+'), $loop);

$read->on('data', function ($data, $read) use ($write, $superFab) {
    if (trim($data) == 'quit') {
        $write->close();
        $read->close();
    }

    $input = trim($data);
    $line = Kitten::get() . ' says "' . $input . '"';
    $line = $superFab->paint($line);
    $line .= PHP_EOL;
    $write->write($line);
});

$loop->run();

