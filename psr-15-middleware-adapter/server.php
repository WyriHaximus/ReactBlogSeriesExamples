<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Message\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;
use ReactParallel\Factory as ParallelFactory;
use ReactParallel\Psr15Adapter\ReactMiddleware;
use WyriHaximus\Psr15\Cat\CatMiddleware;
use WyriHaximus\Psr15\Cowsay\CowsayMiddleware;

require 'vendor/autoload.php';

$loop = Factory::create();
$factory = new ParallelFactory($loop);
$server = new HttpServer(
    $loop,
    new ReactMiddleware(
        $factory,
        new CatMiddleware(),
        new CowsayMiddleware(),
    ),
    static fn (ServerRequestInterface $request): ResponseInterface => new Response(200),
);

$socket = new SocketServer('0.0.0.0:12345', $loop);
$server->listen($socket);

$loop->addTimer(15, static function () use ($loop) {
    $loop->stop();
});

$loop->run();
