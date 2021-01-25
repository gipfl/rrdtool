<?php

namespace gipfl\RrdTool;

use Clue\React\Block;
use Exception;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

trait AsyncHelper
{
    /** @var LoopInterface|null */
    private $loop;

    protected function loop()
    {
        if ($this->loop === null) {
            $this->loop = Factory::create();
        }

        return $this->loop;
    }

    protected function stopLoop()
    {
        if ($this->loop !== null) {
            $this->loop->stop();
        }

        return $this;
    }

    public function waitFor($promises)
    {
        if (! is_array($promises)) {
            $promises = [$promises];
        }
        try {
            Block\awaitAll($promises, $this->loop());
        } catch (Exception $e) {
            $this->showAsyncError($e);

            return false;
        }

        return true;
    }

    public function waitForValues($promises, $allowFailures = false)
    {
        $result = (object) [];
        /** @var PromiseInterface $promise */
        foreach ($promises as $key => $promise) {
            $promise->then(function ($promiseResult) use ($result, $key) {
                $result->$key = $promiseResult;
            }, function (Exception $e) use ($result, $key, $allowFailures) {
                if ($allowFailures) {
                    $result->$key = null;
                } else {
                    throw $e;
                }
            });
        }

        try {
            Block\awaitAll($promises, $this->loop());
        } catch (Exception $e) {
            $this->showAsyncError($e);

            return false;
        }

        return $result;
    }

    /**
     * @param $promise
     * @param bool $allowFailures
     * @return mixed
     */
    public function waitForValue($promise, $allowFailures = false)
    {
        $result = null;
        /** @var PromiseInterface $promise */
        $promise->then(function ($promiseResult) use (&$result) {
            $result = $promiseResult;
        }, function (Exception $e) use ($allowFailures) {
            if (! $allowFailures) {
                throw $e;
            }
        });

        try {
            Block\await($promise, $this->loop());
        } catch (Exception $e) {
            $this->showAsyncError($e);
            // No, really?
            return false;
        }

        return $result;
    }

    protected function showAsyncError(Exception $e)
    {
        if ($this instanceof \gipfl\IcingaWeb2\Widget\ControlsAndContent) {
            $this->content()->add(\ipl\Html\Error::show($e));
        } else {
            echo $e->getMessage() . "\n";
            echo $e->getTraceAsString();
        }
    }
}
