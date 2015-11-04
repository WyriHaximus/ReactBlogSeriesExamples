<?php

echo PHP_EOL;

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new React\HttpClient\Factory();
$client = $factory->create($loop, $dnsResolver);

$size = 0;
$request = $client->request('GET', 'http://download.xs4all.nl/test/100MiB.bin');
$request->on('response', function ($response) use (&$size) {
    $response->on('data', function ($data, $response) use (&$size) {
        $size += strlen($data);
        echo "\033[1A", 'Downloaded size: ',  number_format($size / 1024 / 1024, 2, '.', ''), 'MB', PHP_EOL;
    });
});
$request->end();

$start = time();
$loop->run();
$end = time();

$duration = $end - $start;

echo round($size / 1024 / 1024, 2), 'MB downloaded in ', $duration, ' seconds at ', round(($size / $duration) / 1024 / 1024, 2), 'MB/s', PHP_EOL;
