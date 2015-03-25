<?php

require 'vendor/autoload.php';

$i = 0;
$loop = \React\EventLoop\Factory::create();
$deferred = new \React\Promise\Deferred();

$timer = $loop->addPeriodicTimer(0.01, function(\React\EventLoop\Timer\Timer $timer) use (&$i, $deferred) {
	$deferred->notify($i++);
	if ($i >= 66) {
		$timer->cancel();
		$deferred->resolve();
	}
});

$deferred->promise()->then(function($i) {
	echo 'Done!', PHP_EOL;
}, null, function($i) {
	echo $i, PHP_EOL;
});

$loop->run();
