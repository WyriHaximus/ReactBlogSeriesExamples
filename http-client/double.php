<?php

function call($client, $url) {
    $request = $client->request('GET', $url);
    $request->on('response', function ($response) {
        $response->on('data', function ($data, $response) {
            echo $data;
        });
        sleep(1);
    });
    $request->end();
}

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new React\HttpClient\Factory();
$client = $factory->create($loop, $dnsResolver);

call($client, 'http://blog.wyrihaximus.net/robots.txt');
call($client, 'http://reactphp.org/');

$loop->run();
