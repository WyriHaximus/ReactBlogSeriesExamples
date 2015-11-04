<?php

require 'vendor/autoload.php';

const TWITTER_USER_ID = -1; // Use http://gettwitterid.com/ to get the wanted twitter ID
const CONSUMER_KEY = '';
const CONSUMER_SECRET = '';
const TOKEN = '';
const TOKEN_SECRET = '';

function generateHeader($method, $url, $params = null) {
    $consumer = new JacobKiers\OAuth\Consumer\Consumer(CONSUMER_KEY, CONSUMER_SECRET);
    $token = new JacobKiers\OAuth\Token\Token(TOKEN, TOKEN_SECRET);
    $oauthRequest = JacobKiers\OAuth\Request\Request::fromConsumerAndToken($consumer, $token, $method, $url, $params);
    $oauthRequest->signRequest(new JacobKiers\OAuth\SignatureMethod\HmacSha1(), $consumer, $token);
    return trim(substr($oauthRequest->toHeader(), 15));
}

function handleTweet($client, $tweet) {
    if (isset($tweet->user->screen_name)) {
        echo $tweet->user->screen_name, ': ', $tweet->text, PHP_EOL;
        if (trim($tweet->text) == 'exit();') {
            echo 'exit(); found, stopping...', PHP_EOL;
            die();
        }
        foreach ($tweet->entities->urls as $url) {
            if (substr($url->expanded_url, 0, 36) == 'https://atlas.ripe.net/measurements/') {
                getMeasurement($client, trim(substr($url->expanded_url, 36), '/'));
            }
            if (substr($url->expanded_url, 0, 30) == 'https://atlas.ripe.net/probes/') {
                getProbe($client, trim(substr($url->expanded_url, 30), '/'));
            }
        }
    }
}

function getMeasurement($client, $id) {
    $request = $client->request('GET', 'https://atlas.ripe.net/api/v1/measurement/' . $id . '/');
    $request->on('response', function($response) {
        $buffer = '';
        $response->on('data', function($data) use (&$buffer) {
            $buffer .= $data;
        });
        $response->on('end', function () use (&$buffer) {
            $json = json_decode($buffer);
            echo 'Measurement #', $json->msm_id, ' "', $json->description, '" had ', $json->participant_count, ' nodes involved', PHP_EOL;
        });
    });
    $request->end();
}

function getProbe($client, $id) {
    $request = $client->request('GET', 'https://atlas.ripe.net/api/v1/probe/' . $id . '/');
    $request->on('response', function($response) {
        $buffer = '';
        $response->on('data', function($data) use (&$buffer) {
            $buffer .= $data;
        });
        $response->on('end', function () use (&$buffer) {
            $json = json_decode($buffer);
            echo 'Probe #', $json->id, ' connected since ' . date('r', $json->status_since), PHP_EOL;
        });
    });
    $request->end();
}

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $loop);

$factory = new React\HttpClient\Factory();
$client = $factory->create($loop, $dnsResolver);

$postData = 'follow=' . TWITTER_USER_ID;

$method = 'POST';
$url = 'https://stream.twitter.com/1.1/statuses/filter.json';
$headers = [
    'Authorization' => generateHeader($method, $url, [
        'follow' => TWITTER_USER_ID,
    ]),
    'Content-Type' =>  'application/x-www-form-urlencoded',
    'Content-Length' => strlen($postData),
];
$buffer = '';
$request = $client->request($method, $url, $headers, '1.1');
$request->on('response', function($response) use (&$buffer, $client) {
    echo 'Connected to twitter, listening in on stream:', PHP_EOL;
    $response->on('data', function($data) use (&$buffer, $client) {
        $buffer .= $data;
        if (strpos($buffer, PHP_EOL) !== false) {
            $tweets = explode(PHP_EOL, $buffer);
            $buffer = array_pop($tweets);
            foreach ($tweets as $tweet) {
                if (strlen(trim($tweet)) > 0) {
                    handleTweet($client, json_decode($tweet));
                }
            }
        }
    });
});
$request->end($postData);

$loop->run();
