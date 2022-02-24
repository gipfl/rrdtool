<?php

namespace gipfl\RrdTool;

use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use React\ChildProcess\Process;
use React\Promise\Deferred;
use React\Promise\ExtendedPromiseInterface;
use React\Promise\Timer;
use React\Stream\WritableResourceStream;
use RuntimeException;
use SplDoublyLinkedList;

class AsyncRrdtool implements LoggerAwareInterface
{
    use LoggerAwareTrait;

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
    protected $currentBuffer = '';

    /** @var SplDoublyLinkedList */
    protected $pending;

    protected $processStatsLine;

    protected $logCommunication = false;

    protected $spentTimings;

    protected $pendingImageBytes = null;

    public function __construct(string $basedir, string $rrdtool = '/usr/bin/rrdtool', string $socket = null)
    {
        $this->setLogger(new NullLogger());
        $this->socket = $socket;
        $this->basedir = $basedir;
        $this->rrdtool = $rrdtool;
        $this->pending = new SplDoublyLinkedList();
    }

    public function info($filename): ExtendedPromiseInterface
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
            // $this->logger->info($this->processStatsLine);
            $response = [];
            foreach ($results as $key => $result) {
                if ($result === false) {
                    $response[$key] = false;
                } else {
                    $response[$key] = RrdInfo::parse($result);
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
        $this->currentBuffer = '';
        $this->pendingImageBytes = null;
        $cmd = $this->rrdtool . ' -';
        $this->logger->debug("AsyncRrdtool will run '$cmd'");

        $exitHandler = function ($exitCode, $termSignal) {
            $delay = 5;
            if ($exitCode === null) {
                if ($termSignal === null) {
                    $this->logger->error('rrdtool died');
                    // $event->setLastExitCode(255);
                } else {
                    $this->logger->error("rrdtool got terminated with SIGNAL $termSignal");
                    // $event->setLastExitCode(128 + $termSignal);
                }
            } else {
                if ($exitCode === 0) {
                    $delay = 0;
                }
                $this->logger->warning("exited with exit code $exitCode");
            }

            if (! $this->terminating) {
                $this->logger->info("Stopped unexpectedly, (NOT) restarting in $delay seconds");
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

        $process = new Process("exec $cmd", $this->getBasedir(), $this->getEnv());
        $process
            ->on('error', $errorHandler)
            ->on('exit', $exitHandler)
            ->start();
        $process->stdout->on('data', $stdOutHandler);
        $process->stderr->on('data', $stdErrHandler);

        $this->process = $process;
    }

    protected function processData($data)
    {
        if ($this->logCommunication) {
            $this->logger->debug("< $data");
        }
        $this->buffer .= $data;
        $this->processBuffer();
    }

    protected function processStdErr($data)
    {
        $this->logger->error("STDERR << $data");
    }

    protected function consumeBinaryBuffer()
    {
        $bufferLength = strlen($this->buffer);
        if ($bufferLength < $this->pendingImageBytes) {
            $this->currentBuffer .= $this->buffer;
            $this->pendingImageBytes -= $bufferLength;
            $this->buffer = '';
            // $this->logger->info(sprintf('Got %dbytes, missing %d', $bufferLength, $this->pendingImageBytes));
            return;
        } else {
            // $this->logger->info(sprintf('Buffer has full image, adding missing %dbytes', $this->pendingImageBytes));
            $this->currentBuffer .= substr($this->buffer, 0, $this->pendingImageBytes);
            $this->buffer = substr($this->buffer, $this->pendingImageBytes);
            /*
            $this->logger->info(sprintf(
                'Got all, binary is done. Buffer has %d bytes: %s',
                strlen($this->buffer),
                var_export(substr($this->buffer, 0, 60), 1)
            ));
            */
            $this->pendingImageBytes = null;
        }
    }

    protected function processBuffer()
    {
        $blobPrefix = 'image = BLOB_SIZE:';
        $eol = "\n";
        if ($this->pendingImageBytes) {
            $this->consumeBinaryBuffer();
        }
        if ($this->pendingImageBytes) {
            return;
        }

        $offset = 0;
        while (false !== ($pos = \strpos($this->buffer, $eol, $offset))) {
            $line = \substr($this->buffer, $offset, $pos - $offset);
            $offset = $pos + 1;

            // Let's handle valid results
            if (\substr($line, 0, 3) === 'OK ') {
                // OK u:1.14 s:0.07 r:1.21
                // Might be 1,14 with different locale
                $this->processStatsLine = \substr($line, 3);
                $this->parseTimings($line);
                // TODO: add "\n" ?
                $this->logger->info(sprintf('Got OK, resolving with %dbytes', strlen($this->currentBuffer)));
                $this->resolveNextPending($this->currentBuffer);
                $this->currentBuffer = '';
            } elseif (\substr($line, 0, 7) === 'ERROR: ') {
                $this->rejectNextPending($line);
                $this->currentBuffer = '';
            } elseif (substr($line, 0, strlen($blobPrefix)) === $blobPrefix) {
                $this->pendingImageBytes = (int) substr($line, strlen($blobPrefix));
                $this->logger->info(sprintf('Waiting for an image, %dbytes: %s', $this->pendingImageBytes, $line));
                $this->currentBuffer .= $line . $eol;
                $this->buffer = \substr($this->buffer, $offset);
                $this->consumeBinaryBuffer();
                $this->processBuffer();
                return;
            } else {
                $this->currentBuffer .= $line . $eol;
            }
        }

        if ($offset !== 0) {
            $this->buffer = \substr($this->buffer, $offset);
        }
    }

    public function getTotalSpentTimings()
    {
        return $this->spentTimings;
    }

    /**
     * Line saying OK u:1.14 s:0.07 r:1.21
     * This can be is localized:
     * OK u:0,02 s:0,00 r:0,01
     * OK u:0.02 s:0.00 r:0.01
     *
     * @param $line
     */
    protected function parseTimings($line)
    {
        if (preg_match('/^OK\su:([0-9.,]+)\ss:([0-9.,]+)\sr:([0-9.,]+)$/', $line, $m)) {
            $this->spentTimings = (object) [
                'user' => static::parseLocalizedFloat($m[1]),
                'system' => static::parseLocalizedFloat($m[2]),
                'real' => static::parseLocalizedFloat($m[3]),
            ];
        } else {
            $this->logger->error("Invalid timings: $line");
            $this->spentTimings = null;
        }
    }

    // duplicate
    public static function parseLocalizedFloat($string)
    {
        return (float) \str_replace(',', '.', $string);
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
        $pending = [];
        $deferred = new Deferred();

        foreach ($commands as $key => $command) {
            // $this->logger->debug(\sprintf("Running %s: ", $command));
            $pending[$key] = $this
                ->send($command)
                ->then(function ($result) use (&$results, &$pending, $key) {
                    $results[$key] = $result;
                    unset($pending[$key]);
                })->otherwise(function (Exception $error) use (&$results, &$pending, $key) {
                    $this->logger->error($error->getMessage());
                    $results[$key] = false;
                    unset($pending[$key]);
                })->always(function () use (&$results, $deferred, &$pending) {
                    if (empty($pending)) {
                        $deferred->resolve($results);
                    }
                });
        }

        return $deferred->promise();
    }

    /**
     * @param $command
     * @return ExtendedPromiseInterface
     */
    public function send($command): ExtendedPromiseInterface
    {
        $this->pending->push($deferred = new Deferred());

        /** @var WritableResourceStream $stdIn */
        $stdIn = $this->getProcess()->stdin;
        if ($this->logCommunication) {
            $this->logger->debug("> $command");
        }
        $stdIn->write("$command\n");

        return Timer\timeout($deferred->promise(), 3);
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

    protected function getFullBuffer(): string
    {
        if (empty($this->currentBuffer)) {
            return $this->buffer;
        }

        return $this->currentBuffer . "\n" . $this->buffer;
    }

    protected function rejectAllPending(Exception $exception)
    {
        while (! $this->pending->isEmpty()) {
            $this->pending->shift()->reject($exception);
        }
    }
}
