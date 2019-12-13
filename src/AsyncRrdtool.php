<?php

namespace gipfl\RrdTool;

use Exception;
// use Icinga\Module\Rrd\Helper\Logging;
use React\ChildProcess\Process;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Timer;
use React\Stream\WritableResourceStream;
use RuntimeException;
use SplDoublyLinkedList;

class AsyncRrdtool
{
    // use Logging;

    /** @var LoopInterface */
    protected $loop;

    /** @var string */
    protected $basedir;

    /** @var string */
    protected $rrdtool;

    /** @var string|null RRDCacheD socket path */
    protected $socket;

    /** @var Process */
    protected $process;

    protected $cmdCount;

    protected $maxCmds = 10000;

    protected $terminating = false;

    /** @var string */
    protected $buffer = '';

    protected $bufferLines = [];

    /** @var SplDoublyLinkedList */
    protected $pending;

    protected $processStatsLine;

    protected $logCommunication = false;

    public function __construct(LoopInterface $loop, $basedir, $rrdtool = '/usr/bin/rrdtool', $socket = null)
    {
        $this->loop = $loop;
        $this->socket = $socket;
        $this->basedir = $basedir;
        $this->rrdtool = $rrdtool;
        $this->pending = new SplDoublyLinkedList();
    }

    public function info($filename)
    {
        return $this->send("info $filename")->then(function ($result) {
            return RrdInfo::parse($result);
        });
    }

    /**
     * @param array $fileNames
     * @return \React\Promise\Promise
     */
    public function infoForMany(array $fileNames)
    {
        $commands = [];
        foreach ($fileNames as $key => $filename) {
            $commands[$key] = "info $filename";
        }
        return $this->sendMany($commands)->then(function ($results) {
            // $this->logger()->info($this->processStatsLine);
            $response = [];
            foreach ($results as $key => $result) {
                if ($result === false) {
                    $response[$key] = false;
                } else {
                    $response[$key] = RrdInfo::parseRrdToolOutput($result);
                }
            }

            return $response;
        });
    }

    /**
     * @return Process
     */
    protected function getProcess()
    {
        if ($this->process === null) {
            $this->startRrdTool();
            // TODO: Not German!
            // $this->process = $this->getWatchDog()->getProcess();
        }

        return $this->process;
    }

    public function getBasedir()
    {
        return $this->basedir;
    }

    protected function getEnv()
    {
        // RRDCACHED_ADDRESS:
        $env = [
            'TZ' => 'Europe/Berlin',
            'LC_ALL' => 'de_DE.utf8',
            // 'LC_ALL' => 'it_IT.utf8',
            // 'LC_ALL' => 'en_US.utf8',
        ];
        if ($this->socket !== null) {
            $env['RRDCACHED_ADDRESS'] = $this->socket;
        }

        return $env;
    }

    protected function startRrdTool()
    {
        $this->terminating = false;
        $this->processStatsLine = null;
        $this->buffer = '';
        $cmd = 'exec ' . $this->rrdtool . ' -';
        // $logger->debug("AsyncRrdtool will run '$cmd'");

        $exitHandler = function ($exitCode, $termSignal) {
            $delay = 5;
            // $logger = $this->logger();
            if ($exitCode === null) {
                if ($termSignal === null) {
                    // $logger->error('rrdtool died');
                    // $event->setLastExitCode(255);
                } else {
                    // $logger->error("rrdtool got terminated with SIGNAL $termSignal");
                    // $event->setLastExitCode(128 + $termSignal);
                }
            } else {
                if ($exitCode === 0) {
                    $delay = 0;
                }
                // $logger->warning("exited with exit code $exitCode");
            }

            if (! $this->terminating) {
                // $logger->info("Stopped unexpectedly, (NOT) restarting in $delay seconds");
            }
            $this->process = null;
            $this->terminating = false;
        };
        $errorHandler = function (Exception $e) {
            $this->process = null;
            throw $e;
        };
        $stdOutHandler = function ($data) {
            $this->processData($data);
        };
        $stdErrHandler = function ($data) {
            $this->processStderr($data);
        };

        $process = new Process($cmd, $this->getBasedir(), $this->getEnv());
        $process
            ->on('error', $errorHandler)
            ->on('exit', $exitHandler)
            ->start($this->loop);
        $process->stdout->on('data', $stdOutHandler);
        $process->stderr->on('data', $stdErrHandler);

        $this->process = $process;
    }

    protected function processData($data)
    {
        if ($this->logCommunication) {
            // $this->logger()->info("< $data");
        }
        $this->buffer .= $data;
        $this->processBuffer();
    }

    protected function processStdErr($data)
    {
        // $this->logger()->error("<< $data");
    }

    protected function processBuffer()
    {
        $offset = 0;

        while (false !== ($pos = \strpos($this->buffer, "\n", $offset))) {
            $line = \substr($this->buffer, $offset, $pos - $offset);
            $offset = $pos + 1;

            // Let's handle valid results
            if (\substr($line, 0, 3) === 'OK ') {
                // OK u:1.14 s:0.07 r:1.21
                // Might be 1,14 with different locale
                $this->processStatsLine = \substr($line, 3);
                // TODO: add "\n" ?
                $this->resolveNextPending(implode("\n", $this->bufferLines));
                $this->bufferLines = [];
            } elseif (\substr($line, 0, 7) === 'ERROR: ') {
                $this->rejectNextPending($line);
                $this->bufferLines = [];
            } else {
                $this->bufferLines[] = $line;
            }
        }

        if ($offset !== 0) {
            $this->buffer = \substr($this->buffer, $offset);
        }
    }

    public function endProcess()
    {
        if ($this->process) {
            $this->terminating = true;
            // TODO: quit or terminate or both?
            // $this->process->stdin->write('quit');
            $this->process->terminate();
            $this->process = null;
        }
    }

    /**
     * @param array $commands
     * @return \React\Promise\Promise
     */
    public function sendMany(array $commands)
    {
        $results = [];
        $logger = null;
        // $logger = $this->logger();
        $pending = [];
        $deferred = new Deferred();

        foreach ($commands as $key => $command) {
            // $logger->debug(\sprintf("Running %s: ", $command));
            $pending[$key] = $this
                ->send($command)
                ->then(function ($result) use (& $results, & $pending, $key, $logger) {
                    $results[$key] = $result;
                    unset($pending[$key]);
                })->otherwise(function ($error) use (& $results, & $pending, $key, $logger) {
                    $results[$key] = false;
                    unset($pending[$key]);
                })->always(function () use (& $results, $deferred, & $pending, $logger) {
                    if (empty($pending)) {
                        $deferred->resolve($results);
                    }
                });
        }

        return $deferred->promise();
    }

    /**
     * @param $command
     * @return \React\Promise\Promise
     */
    public function send($command)
    {
        $this->pending->push($deferred = new Deferred());

        /** @var WritableResourceStream $stdIn */
        $stdIn = $this->getProcess()->stdin;
        if ($this->logCommunication) {
            // $this->logger()->info("> $command");
        }

        $stdIn->write("$command\n");

        return Timer\timeout($deferred->promise(), 3, $this->loop);
    }

    protected function resolveNextPending($result)
    {
        $this->pending->shift()->resolve($result);
    }

    protected function rejectNextPending($message)
    {
        $this->pending->shift()->reject(new RuntimeException($message));
    }

    protected function failForProtocolViolation()
    {
        $exception = new RuntimeException('Protocol exception, got: ' . $this->getFullBuffer());
        $this->rejectAllPending($exception);
        $this->endProcess();
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
        while (! $this->pending->isEmpty()) {
            $this->pending->shift()->reject($exception);
        }
    }
}
