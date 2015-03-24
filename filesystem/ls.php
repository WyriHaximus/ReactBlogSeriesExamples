<?php

require '../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$dir = \React\Filesystem\Filesystem::create($loop)->dir(dirname(__DIR__));
$dir->ls()->then(function ($list) {
    foreach ($list as $node) {
        echo $node->getPath(), PHP_EOL;
    }
}, function ($e) {
    die($e->getMessage() . PHP_EOL);
});

$loop->run();

