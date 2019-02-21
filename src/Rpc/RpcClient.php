<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Connection;
use gipfl\Protocol\NetString\StreamWrapper;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class RpcClient
{
    /** @var \React\EventLoop\LoopInterface */
    protected $loop;

    /** @var Connection */
    protected $jsonRpc;

    protected $testCnt = 0;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    public function connect($host, $port)
    {
        $connector = new Connector($this->loop);
        return $connector->connect("$host:$port")
            ->then(function (ConnectionInterface $connection) {
                $this->jsonRpc = $this->enableProtocol($connection);

                return $this;
            });
    }

    public function request($method, $params = null)
    {
        return $this->jsonRpc->request($method, $params);
    }

    public function graph($params)
    {
        return $this->jsonRpc->request('rrd.graph', $params);
    }

    protected function version()
    {
        return $this->jsonRpc->request('rrd.version');
    }

    protected function enableProtocol(ConnectionInterface $connection)
    {
        $netString = new StreamWrapper($connection);
        $jsonRpc = new Connection();
        $jsonRpc->handle($netString);

        return $jsonRpc;
    }
}
