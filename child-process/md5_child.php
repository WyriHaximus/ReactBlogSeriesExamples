<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$read = new \React\Stream\Stream(STDIN, $loop);
$read->on('data', function ($datas) use ($loop) {
    $datas = explode(PHP_EOL, trim($datas));

    foreach ($datas as $data) {
        if ($data == 15) {
            $loop->stop();
            return;
        }

        for ($i = 0; $i < 100000; $i++) {
            $data = hash('sha256', time() . $data);
        }

        echo $data, PHP_EOL;
    }
});

$loop->run();
