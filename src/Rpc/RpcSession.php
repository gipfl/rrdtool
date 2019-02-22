<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Connection;
use gipfl\Protocol\NetString\StreamWrapper;
use React\Socket\ConnectionInterface;

class RpcSession
{
    /** @var Connection */
    protected $jsonRpc;

    public function __construct(ConnectionInterface $connection)
    {
        $this->jsonRpc = $this->enableProtocol($connection);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->jsonRpc;
    }

    protected function enableProtocol(ConnectionInterface $connection)
    {
        $netString = new StreamWrapper($connection);
        $jsonRpc = new Connection();
        $jsonRpc->handle($netString);

        return $jsonRpc;
    }
}
