<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Connection;
use gipfl\Protocol\NetString\StreamWrapper;
use gipfl\RrdTool\Rrdtool;
use React\Socket\ConnectionInterface;

class RpcSession
{
    protected $jsonRpc;

    public function __construct(ConnectionInterface $connection)
    {
        $this->jsonRpc = $this->enableRpcHandlers(
            $this->enableProtocol($connection)
        );
    }

    protected function enableRpcHandlers(Connection $jsonRpc)
    {
        $jsonRpc->setNamespaceSeparator('.');
        $jsonRpc->setHandler(new RpcHandler($jsonRpc, $rrdtool), 'rrd');

        return $jsonRpc;
    }

    protected function enableProtocol(ConnectionInterface $connection)
    {
        $netString = new StreamWrapper($connection);
        $jsonRpc = new Connection();
        $jsonRpc->handle($netString);

        return $jsonRpc;
    }
}
