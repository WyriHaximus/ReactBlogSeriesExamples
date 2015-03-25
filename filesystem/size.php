<?php

require 'vendor/autoload.php';

$loop = \React\EventLoop\Factory::create();
$dir = \React\Filesystem\Filesystem::create($loop)->dir(dirname(__DIR__));
$dir->lsRecursive()->then(function (\SplObjectStorage $list) {
    $phpFiles = new RegexIterator($list, '/.*?.php$/');
    $promises = [];
    foreach ($phpFiles as $node) {
        if (strpos($node->getPath(), 'vendor') !== false) {
            continue;
        }
        $file = $node;
        $promises[] = $file->size()->then(function ($size) use ($file) {
            echo $file->getPath(), ': ', number_format($size / 1024, 2), 'KB', PHP_EOL;
            return $size;
        });
    }
    \React\Promise\all($promises)->then(function ($sizes) {
        $total = 0;
        foreach ($sizes as $size) {
            $total += $size;
        }
        echo 'Total: ', number_format($total / 1024, 2), 'KB', PHP_EOL;
    });
});

$loop->run();
