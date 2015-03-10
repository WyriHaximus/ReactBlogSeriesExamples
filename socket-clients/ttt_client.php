<?php

require '../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$dnsResolverFactory = new React\Dns\Resolver\Factory();
$dns = $dnsResolverFactory->createCached('8.8.8.8', $loop);
$connector = new React\SocketClient\Connector($loop, $dns);

$connector->create('127.0.0.1', 1337)->then(function (React\Stream\Stream $stream) {
    $buffer = '';
    $stream->on('data', function ($data, $stream) use (&$buffer) {
        echo $data;
        $cols = [ 'a', 'b', 'c'];
        $rows = [1, 2, 3];

        $buffer .= $data;

        if (strpos($buffer, PHP_EOL) !== false) {
            $chunks = explode(PHP_EOL, $buffer);
            $buffer = array_pop($chunks);
            foreach ($chunks as $chunk) {
                if (trim($chunk) == 'Your turn:') {
                    $stream->write($cols[mt_rand(0 ,2)] . $rows[mt_rand(0 ,2)] . PHP_EOL);
                }
            }
        }
    });
});

$loop->run();
