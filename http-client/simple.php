<?php

use React\Dns\Resolver\Factory as DNSResolverFactory;
use React\EventLoop\Factory as EventLoopFactory;
use React\HttpClient\Factory as HttpClientFactory;

require 'vendor/autoload.php';

$loop = EventLoopFactory::create();

$dnsResolverFactory = new DNSResolverFactory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new HttpClientFactory();
$client = $factory->create($loop, $dnsResolver);

$request = $client->request('GET', 'https://example.com/');
$request->on('response', function ($response) {
    $response->on('data', function ($data, $response) {
        echo $data;
    });
});
$request->end();

$loop->run();
