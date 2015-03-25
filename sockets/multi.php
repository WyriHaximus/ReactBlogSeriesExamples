<?php

class Connection
{
    protected $colour;
    protected $connections;

    public function __construct($conn, $colour, $connections)
    {
        $this->colour = $colour;
        $this->connections = $connections;

        $this->connections->attach($conn);

        // Event listener for incoming data
        $conn->on('data', [$this, 'onData']);

        // Handle the on close event and remove the connection from the connections pool
        $conn->on('close', [$this, 'onClose']);
    }

    public function onData($data)
    {
        // Write data that came in from this connection into all connection
        foreach ($this->connections as $connection) {
            $connection->write($data);
        }

        // Echo the data into our terminal window
        echo (new Malenki\Ansi($data))->fg($this->colour);
    }

    public function onClose($conn)
    {
        $this->connections->detach($conn);
    }
}

require 'vendor/autoload.php';

$colours = ['red', 'green', 'yellow', 'blue', 'purple', 'cyan'];
$connections = new \SplObjectStorage();

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

// This event triggers every time a new connection comes in
$socket->on('connection', function ($conn) use (&$colours, $connections) {
    $colour = array_pop($colours); // Only doing this as an example, you will run out of colours.

    // Instancing a new connection object per connection
    new Connection($conn, $colour, $connections);
});

// Listen on port 1337
$socket->listen(1337);

$loop->run();
