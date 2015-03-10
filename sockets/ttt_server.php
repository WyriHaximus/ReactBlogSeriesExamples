<?php

require '../vendor/autoload.php';

use React\EventLoop\Factory;
use React\Promise\Deferred;
use React\Socket\Connection;
use React\Socket\Server;
use WyriHaximus\TicTacToe\Game;
use WyriHaximus\TicTacToe\Player;
use WyriHaximus\TicTacToe\Ui;

class PlayerConnection extends Player
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Game
     */
    protected $game;

    /**
     * @var string
     */
    protected $buffer = '';

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->on('data', [$this, 'onData']);
        $this->connection->write('Welcome to Tic tac Toe, the game will start when two players are connected!' . PHP_EOL);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function move(Game $game)
    {
        $this->game = $game;
        $this->writeBoard();
        $this->connection->write('Your turn:' . PHP_EOL);
    }

    public function onData($data)
    {
        $this->buffer .= $data;
        $this->buffer = strtolower($this->buffer);

        if (strpos($this->buffer, PHP_EOL) !== false) {
            $chunks = explode(PHP_EOL, $this->buffer);
            $this->buffer = array_pop($chunks);
            foreach ($chunks as $chunk) {
                list($col, $row) = str_split($chunk);

                try {
                    $this->game->move($this, [
                        'col' => $col,
                        'row' => $row,
                    ]);
                    $this->writeBoard();
                } catch (\InvalidArgumentException $e) {
                    $this->move($this->game);
                }
            }
        }
    }

    protected function writeBoard()
    {
        $ui = Ui::printStatus($this->game);
        $this->connection->write($ui);
        echo $ui;
    }
}

$loop = Factory::create();
$socket = new Server($loop);

$players = [];

$socket->on('connection', function (Connection $conn) use (&$players, $socket) {
    if (count($players) < 2) {
        $players[] = new PlayerConnection($conn);
    }

    if (count($players) == 2) {
        $deferred = new Deferred();
        $deferred->promise()->then(function ($results) use (&$players, $socket) {
            foreach ($results as $result) {
                foreach ($players as $player) {
                    if ($player === $result[0]) {
                        $player->getConnection()->end('You ' . $result[1] . PHP_EOL);
                    }
                }
            }
            $socket->shutdown();
        });
        $game = new Game($players[0], $players[1]);
        $game->start($deferred);
    }
});

$socket->listen(1337, '0.0.0.0');

$loop->run();
