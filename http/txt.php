<?php

use React\EventLoop\Factory;
use React\Filesystem\Filesystem;
use React\Filesystem\Node\File;
use React\Http\Request;
use React\Http\Response;

define('WEBROOT', __DIR__ . DIRECTORY_SEPARATOR . 'webroot');

require 'vendor/autoload.php';

$loop = Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket, $loop);
$filesystem = Filesystem::create($loop);
$files = $filesystem->dir(WEBROOT)->ls();

$http->on('request', function (Request $request, Response $response) use ($filesystem, $files) {
    echo 'Request for: ' . $request->getPath(), PHP_EOL;
    $files->then(function (SplObjectStorage $files) use ($filesystem, $request) {
        foreach ($files as $file) {
            if ($file->getPath() == WEBROOT . $request->getPath()) {
                return $file;
            }
        }

        return $filesystem->file(WEBROOT . DIRECTORY_SEPARATOR . '404.txt');
    })->then(function (File $file) {
        return $file->getContents()->then(function ($contents) use ($file) {
            return $file->close()->then(function () use ($contents) {
                return $contents;
            });
        });
    })->then(function ($fileContents) use ($response) {
        $response->writeHead(200, ['Content-Type' => 'text/plain']);
        $response->end($fileContents);
    });
});

$socket->listen(1337);
$loop->run();
