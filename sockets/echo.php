<?php

require '../vendor/autoload.php';

$colours = ['red', 'green', 'yellow', 'blue', 'purple', 'cyan'];

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

// This event triggers every time a new connection comes in
$socket->on('connection', function ($conn) use ($colours) {
    $colour = array_pop($colours); // Only doing this as an example, you will run out of colours.

    // Event listener for incoming data
    $conn->on('data', function ($data, $conn) use ($colour) {
        // Write data back to the connection
        $conn->write($data);

        // Echo the data into our terminal window
        echo (new \Malenki\Ansi($data))->fg($colour);
    });
});

// Listen on port 1337
$socket->listen(1337);

$loop->run();
