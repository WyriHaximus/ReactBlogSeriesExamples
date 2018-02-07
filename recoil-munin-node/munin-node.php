<?php

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use function React\Promise\Stream\first;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;
use Recoil\React\ReactKernel;

final class MuninNodeSnmp
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Connector
     */
    private $connector;

    /**
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->connector = new Connector($loop);
    }

    public function gather(string $node)
    {
        // Split out the node host:post and node within the node
        list ($node, $prefix) = explode('|', $node);

        /** @var ConnectionInterface $connection */
        $connection = yield $this->connect($node);

        $ports = [];
        foreach (yield $this->fetchPorts($connection, $prefix) as $port) {
            $ports[$port] = yield $this->fetch($connection, $port);
        }

        // Close the connection now that we're done
        $connection->write('quit' . "\n");

        return $ports;
    }

    private function connect(string $node)
    {
        /** @var ConnectionInterface $connection */
        $connection = yield $this->connector->connect($node);

        /* Read the munin-node welcome message and discard it */
        yield first($connection);

        // Return the connect now that the welcome message has been received
        return $connection;
    }

    private function fetchPorts(ConnectionInterface $connection, string $prefix)
    {
        // Request a list of items
        $connection->write("list $prefix\n");
        $buffer = '';
        do {
            // We've calling the React\Promise\Stream\first function here which returns a
            // promise resolving on the first data event, or on the event name you give it as second parameter.
            $buffer .= yield first($connection);
            // Stop once we fond `_uptime`, which is the last in the list
        } while (substr(trim($buffer), -7) != '_uptime');

        $ports = [];
        // Cut the response line in an array of items and iterate of it
        foreach (explode(' ', $buffer) as $port) {
            // Filter out any unwanted items such as error count and uptime
            if (strpos($port, '_err_') !== false) {
                continue;
            }
            if (strpos($port, '_uptime') !== false) {
                continue;
            }

            // Filter out LAG ports (LAG ports are bounded ports for more performance or resilience)
            $chunks = explode('_', $port);
            if ((int)$chunks[count($chunks) - 1] > 25) {
                continue;
            }

            $ports[] = $port;
        }

        return $ports;
    }

    private function fetch(ConnectionInterface $connection, string $name)
    {
        // Request values for the given $name, in our case a port
        $connection->write('fetch ' . $name . "\n");

        $buffer = '';
        // Keep going until we find a period as last value
        do {
            $buffer .= yield first($connection);
        } while (substr(trim($buffer), -1) != '.');

        // Strip any new lines and periods from the outer bounds of the buffer
        $buffer = trim($buffer);
        $buffer = trim($buffer, '.');
        $buffer = trim($buffer);

        $throughput = 0;
        // Iterate through all returned values and combine their values in $throughput
        foreach (explode("\n", $buffer) as $line) {
            list($name, $counter) = explode(' ', $line);
            // Ignore any lines which have the value U
            if ($counter == 'U') {
                continue;
            }

            // Increase the throughput counter, this includes both sent and receive counters (we want both)
            $throughput += $counter;
        }

        return $throughput;
    }
}

require 'vendor/autoload.php';

$loop = Factory::create();
$muninNode = new MuninNodeSnmp($loop);

$kernel = ReactKernel::create($loop);
$kernel->execute(function () use ($muninNode, $argv) {
    $data = [];
    // Iterate of all arguments, the following syntax is assume "munin-node-host:port|switch-ip"
    // The switch IP is needed for the list command
    for ($i = 1; $i < count($argv); $i++) {
        $data[$argv[$i]] = yield $muninNode->gather($argv[$i]);
    }

    var_export($data);
});

$loop->run();
