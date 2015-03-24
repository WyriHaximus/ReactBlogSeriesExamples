<?php

require '../vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$dir = \React\Filesystem\Filesystem::create($loop)->dir(dirname(__DIR__));
$dir->lsRecursive()->then(function (\SplObjectStorage $list) {
    $phpFiles = new RegexIterator($list, '/.*?.php$/');
    foreach ($phpFiles as $node) {
        if (strpos($node->getPath(), 'vendor') !== false) {
            continue;
        }
        echo $node->getPath(), PHP_EOL;
    }
});

$loop->run();
