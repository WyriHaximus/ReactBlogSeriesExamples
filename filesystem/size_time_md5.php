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
        $contents = $file->getContents()->then(function ($contents) {
            return md5($contents);
        });
        $promises[] = \React\Promise\all([$file->stat(), $contents])->then(function ($data) use ($file) {
            list ($stat, $md5) = $data;
            echo substr($file->getPath(), strlen(dirname(__DIR__)));
            echo ': ', number_format($stat['size'] / 1024, 2), 'KB, ';
            echo 'md5 hash:', $md5, ', ';
            echo 'access time: ', (new DateTime('@' . $stat['atime']))->format('r'), PHP_EOL;
            $file->touch();
            return $stat['size'];
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