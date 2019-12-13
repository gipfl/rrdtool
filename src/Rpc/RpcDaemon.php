<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\RrdTool\RrdCached\Client;
use gipfl\RrdTool\Rrdtool;
use Icinga\Application\Logger;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use SplObjectStorage;

class RpcDaemon
{
    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var SplObjectStorage */
    protected $connections;

    /** @var Rrdtool */
    protected $rrdtool;

    /** @var Client */
    protected $client;

    public function __construct(Rrdtool $rrdtool, Client $client)
    {
        $this->rrdtool = $rrdtool;
        $this->client = $client;
    }

    public function run(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->loop->futureTick(function () {
            // TODO: try and retry forever
            $this->listen();
        });
        $this->loop->run();
    }

    protected function listen()
    {
        $host = '0.0.0.0';
        $port = 5663;
        $socket = new Server("$host:$port", $this->loop);
        $this->connections = new SplObjectStorage();
        Logger::info("Starting RPC listener on $host:$port");
        $socket->on('connection', function (ConnectionInterface $connection) {
            $this->connections->attach($connection);
            Logger::debug('RPC connection from ' . $connection->getRemoteAddress());
            $session = new RpcSession($connection);
            $session->getConnection()
                ->setNamespaceSeparator('.')
                ->setHandler(new RpcHandler($this->rrdtool), 'rrdtool');

            $connection->on('close', function () use ($session, $connection) {
                Logger::debug('Closing RPC session for ' . $connection->getRemoteAddress());
                unset($session);
                if ($this->connections) {
                    $this->connections->detach($connection);
                }
            });
        });
    }
}
