<?php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$dns = (new React\Dns\Resolver\Factory())->create('8.8.8.8', $loop);
$promises = [];

foreach([
    'example.com',
    'blog.wyrihaximus.net',
    'wyrihaximus.net',
] as $host) {
    $hostname = $host;
    $promises[] = $dns->resolve($hostname)->then(
        function($ip) use ($hostname) {
            echo 'The IP address for ' . $hostname . ' is: ' . $ip, PHP_EOL;
            return $hostname;
        }
    );
}

\React\Promise\all($promises)->then(function($hostnames) {
    echo 'Done: ' . implode(', ', $hostnames) . '!', PHP_EOL;
});

$loop->run();