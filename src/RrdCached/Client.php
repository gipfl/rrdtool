<?php

namespace gipfl\RrdTool\RrdCached;

use Exception;
use gipfl\Protocol\JsonRpc\Error;
use gipfl\RrdTool\DsList;
use gipfl\RrdTool\RraSet;
use gipfl\RrdTool\RrdInfo;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use function React\Promise\Timer\timeout;
use React\Socket\ConnectionInterface;
use React\Socket\UnixConnector;
use RuntimeException;

class Client
{
    use LoggerAwareTrait;

    protected $socketFile;

    /** @var LoopInterface */
    protected $loop;

    /** @var ConnectionInterface */
    protected $connection;

    /** @var \React\Promise\Promise|null */
    protected $currentBatch;

    /** @var Deferred[] */
    protected $pending = [];

    protected $pendingLines = [];

    protected $buffer = '';

    protected $bufferLines = [];

    protected $availableCommands;

    public function __construct($socketFile, LoopInterface $loop)
    {
        $this->socketFile = $socketFile;
        $this->loop = $loop;
        $this->setLogger(new NullLogger());
    }

    /**
     * Returns a Promise giving RRDCacheD stats as an associative Array
     *
     * Usually looks like this:
     * <code>
     * [
     *     'QueueLength'     => 0,
     *     'UpdatesReceived' => 190571,
     *     'FlushesReceived' => 41,
     *     'UpdatesWritten'  => 1406,
     *     'DataSetsWritten' => 167482,
     *     'TreeNodesNumber' => 9,
     *     'TreeDepth'       => 4,
     *     'JournalBytes'    => 16306456,
     *     'JournalRotate'   => 12,
     * ];
     * </code>
     *
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function stats()
    {
        return $this->send("STATS")->then(function ($result) {
            $pairs = [];
            foreach ($result as $line) {
                list($key, $value) =  \preg_split('/:\s/', $line, 2);
                $pairs[$key] = (int) $value;
            }

            return $pairs;
        });
    }

    /**
     * When resolved returns true on success, otherwise an Array with errors
     *
     * The error Array uses the original line number starting from 1 as a key,
     * giving each individual error as it's value
     *
     * Might look like this:
     * <code>
     * [
     *     4 => 'Can\'t use \'flushall\' here.',
     *     9 => 'No such file: /path/to/file.rrd'
     * ];
     * </code>
     *
     * @param string|array $commands
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function batch($commands)
    {
        if ($this->currentBatch !== null) {
            return $this->currentBatch->then(function () use ($commands) {
                // Logger::warning(
                //     'RRDCacheD: a BATCH is already in progress, queuing up.'
                //     . ' This could be a bug, please let us know!'
                // );

                return $this->batch($commands);
            });
        }

        // TODO: If a command manages it to be transmitted between "BATCH" and
        // it's commands, this could be an undesired race condition. We should
        // either combine both strings and parse two results - or implement some
        // other blocking logic.
        if (\is_array($commands)) {
            if (empty($commands)) {
                throw new RuntimeException('Cannot run BATCH with no command');
            }
            $commands = \implode("\n", $commands) . "\n.";
        } else {
            $commands = \rtrim($commands, "\n") . "\n.";
        }

        // BATCH gives: 0 Go ahead.  End with dot '.' on its own line.
        $this->currentBatch = $this->send('BATCH')->then(function ($result) use ($commands) {
            return $this->send($commands)->then(function ($result) {
                if ($result === 'errors' || $result === true) { // TODO: either one or the other
                    // Was: '0 errors'
                    return true;
                } elseif (\is_string($result)) {
                    // Well... unknown string, but anyways - no error
                    return true;
                } elseif (\is_array($result)) {
                    $res = [];
                    foreach ($result as $line) {
                        if (\preg_match('/^(\d+)\s(.+)$/', $line, $match)) {
                            $res[(int) $match[1]] = $match[2];
                        } else {
                            throw new RuntimeException(
                                'Unexpected result from BATCH: ' . \implode("\n", $result) . "\n"
                            );
                        }
                    }

                    return $res;
                } else {
                    throw new RuntimeException('Unexpected result from BATCH: ' . \var_export($result, 1));
                }
            })->always(function () {
                $this->currentBatch = null;
            });
        });

        return $this->currentBatch;
    }

    /**
     * When resolved returns true on success
     *
     * This doesn't mean that all files have been flushed, but that FLUSHALL has
     * successfully been started.
     *
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function flushAll()
    {
        return $this->send("FLUSHALL")->then(function ($result) {
            // $result is 'Started flush.'
            return true;
        });
    }

    /**
     * When resolved usually returns 'PONG'
     *
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function ping()
    {
        return $this->send("PING");
    }

    public function first($file, $rra = 0)
    {
        $file = static::quoteFilename($file);

        return $this->send("FIRST $file $rra")->then(function ($result) {
            return (int) $result;
        });
    }

    /**
     * @param $file
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function last($file)
    {
        $file = $this->quoteFilename($file);

        return $this->send("LAST $file")->then(function ($result) {
            return (int) $result;
        })->otherwise(function (Exception $e) {
            // -1 Error: rrdcached: Invalid timestamp returned
            return Error::forException($e);
        });
    }

    /**
     * @param $file
     * @return \React\Promise\Promise
     */
    public function flush($file)
    {
        $file = static::quoteFilename($file);

        return $this->send("FLUSH $file")->then(function ($result) {
            // $result is 'Successfully flushed <path>/<filename>.rrd.'
            return true;
        });
    }

    /**
     * @param $file
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function forget($file)
    {
        $file = static::quoteFilename($file);

        return $this->send("FORGET $file")->then(function ($result) {
            // $result is 'Gone!'
            return true;
        })->otherwise(function ($result) {
            // $result is 'No such file or directory'
            return false;
        });
    }

    /**
     * @param $file
     * @return \React\Promise\ExtendedPromiseInterface
     */
    public function flushAndForget($file)
    {
        $file = static::quoteFilename($file);

        return $this->flush($file)->then(function () use ($file) {
            return $this->forget($file);
        });
    }

    public function pending($file)
    {
        $file = static::quoteFilename($file);
        return $this->send("PENDING $file")->then(function ($result) {
            if (is_array($result)) {
                return $result;
            } else {
                // '0 updates pending', so $result is 'updates pending'
                return [];
            }
        })->otherwise(function () {
            return [];
        });
    }

    public function info($file)
    {
        return $this->rawInfo($file)->then(function ($result) {
            return RrdInfo::parseLines($result);
        });
    }

    public function rawInfo($file)
    {
        $file = static::quoteFilename($file);

        return $this->send("INFO $file");
    }

    /**
     * @param $filename
     * @param $step
     * @param $start
     * @param DsList $dsList
     * @param RraSet $rraSet
     * @return \React\Promise\Promise
     */
    protected function createFile($filename, $step, $start, DsList $dsList, RraSet $rraSet)
    {
        return $this->send(\sprintf(
            "CREATE %s -s %d -b %d %s %s",
            static::quoteFilename($filename),
            $step,
            $start,
            $dsList,
            $rraSet
        ));
    }

    public static function quoteFilename($filename)
    {
        // TODO: do we need to escape/quote here?
        return \addcslashes($filename, ' ');
        return "'" . addcslashes($filename, "'") . "'";
    }

    public function listAvailableCommands()
    {
        // This doesn't work!?
        // if ($this->availableCommands !== null) {
        //     return resolve($this->availableCommands);
        // }

        return $this->availableCommands = $this->send("HELP")->then(function ($result) {
            $result = \array_map(static function ($value) {
                return \preg_replace('/\s.+$/', '', $value);
            }, $result);
            \sort($result);

            return $result;
        });
    }

    public function hasCommand($commandName)
    {
        return $this
            ->listAvailableCommands()
            ->then(function ($commands) use ($commandName) {
                return \in_array($commandName, $commands);
            });
    }

    public function listRecursive($directory = '/')
    {
        return $this->send("LIST RECURSIVE $directory")->then(function ($result) {
            \sort($result);

            return $result;
        });
    }

    public function quit()
    {
        if ($this->connection !== null) {
            $this->connection->write("quit");
        }
    }

    /**
     * @param $command
     * @return \React\Promise\Promise
     */
    public function send($command)
    {
        $this->pending[] = $deferred = new Deferred();

        $command = \rtrim($command, "\n") . "\n";
        // Logger::debug
        // echo "Sending $command\n";

        // foreach (\preg_split('/\r\n/', "$command") as $l) {
        //     echo "> $l\n";
        // }
        if ($this->connection === null) {
            $this->logger->debug("Not yet connected, deferring $command");
            $this->connect()->then(function () use ($command, $deferred) {
                $this->logger->debug('Connected to RRDCacheD, now sending ' . trim($command));
                $this->connection->write("$command");
            })->otherwise(function (Exception $error) use ($deferred) {
                $this->logger->error('Connection to RRDCacheD failed');
                $deferred->reject($error);
            });
        } else {
            // TODO: Drain if false?
            $this->connection->write("$command");
        }

        // Hint: used to be 5s, too fast?
        return timeout($deferred->promise(), 30, $this->loop);
    }

    protected function connect()
    {
        $this->availableCommands = null;
        $connector = new UnixConnector($this->loop);

        $attempt = $connector->connect($this->socketFile)->then(function (ConnectionInterface $connection) {
            $connection->on('end', function () {
                $this->logger->info('RRDCacheD Client ended');
                $this->connection = null;
            });
            $connection->on('error', function (\Exception $e) {
                $this->logger->error('RRDCacheD Client error: ' . $e->getMessage());
                $this->connection = null;
            });

            $connection->on('close', function () {
                $this->logger->info('RRDCacheD Client closed');
                $this->connection = null;
            });

            $this->connection = $connection;
            $this->initializeHandlers($connection);
        })->otherwise(function (Exception $e) {
            $this->logger->error('RRDCached connection error: ' . $e->getMessage());
        });

        return timeout($attempt, 5, $this->loop);
    }

    protected function initializeHandlers(ConnectionInterface $connection)
    {
        $connection->on('data', function ($data) {
            $this->processData($data);
        });

        // $connection->on('error', fail all pending, reconnect?)
    }

    protected function processData($data)
    {
        $this->buffer .= $data;
        $this->processBuffer();
    }

    protected function processBuffer()
    {
        $offset = 0;

        while (false !== ($pos = \strpos($this->buffer, "\n", $offset))) {
            $line = \substr($this->buffer, $offset, $pos - $offset);
            $offset = $pos + 1;
            $this->bufferLines[] = $line;
        }

        if ($offset > 0) {
            $this->buffer = \substr($this->buffer, $offset);
        }

        $this->checkForResults();
    }

    protected function checkForResults()
    {
        while (! empty($this->bufferLines)) {
            $current = $this->bufferLines[0];
            $pos = \strpos($current, ' ');
            if ($pos === false) {
                $this->failForProtocolViolation();
            }
            $cntLines = \substr($current, 0, $pos);
            // echo "< " . $current . "\n";
            if ($cntLines === '-1') {
                \array_shift($this->bufferLines);
                $this->rejectNextPending(\substr($current, $pos + 1));
            } elseif (\ctype_digit($cntLines)) {
                $cntLines = (int) $cntLines;

                if ($cntLines === 0) {
                    if (empty($this->pending)) {
                        $this->failForProtocolViolation();
                    }

                    \array_shift($this->bufferLines);
                    $result = \substr($current, $pos + 1);
                    if ($result === 'errors') { // Output: 0 errors
                        $result = true;
                    }
                    $this->resolveNextPending($result);

                    continue;
                }
                if (\count($this->bufferLines) <= $cntLines) {
                    // We'll wait, there are more lines to come
                    return;
                }

                if (empty($this->pending)) {
                    $this->failForProtocolViolation();
                }

                \array_shift($this->bufferLines);
                $result = [];
                for ($i = 0; $i < $cntLines; $i++) {
                    $result[] = \array_shift($this->bufferLines);
                }

                $this->resolveNextPending($result);
            } else {
                \array_shift($this->bufferLines);
                $this->failForProtocolViolation();
            }
        }
    }

    protected function resolveNextPending($result)
    {
        if (empty($this->pending)) {
            $this->failForProtocolViolation();
        }
        $next = \array_shift($this->pending);
        $next->resolve($result);
    }

    protected function rejectNextPending($message)
    {
        $next = \array_shift($this->pending);
        $this->logger->debug("Rejecting: $message");
        $next->reject(new RuntimeException($message));
    }

    protected function failForProtocolViolation()
    {
        $exception = new RuntimeException('Protocol exception, got: ' . $this->getFullBuffer());
        $this->rejectAllPending($exception);
        $this->connection->close();
        unset($this->connection);
    }

    protected function getFullBuffer()
    {
        if (empty($this->bufferLines)) {
            return $this->buffer;
        } else {
            return \implode("\n", $this->bufferLines) . "\n" . $this->buffer;
        }
    }

    protected function rejectAllPending(Exception $exception)
    {
        foreach ($this->pending as $deferred) {
            $deferred->reject($exception);
        }
    }
}
