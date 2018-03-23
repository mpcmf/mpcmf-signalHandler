<?php

namespace mpcmf\system\helper\service;

use mpcmf\system\pattern\singleton;

class signalHandler
{

    use singleton;

    /**
     * @var callable[][]
     */
    protected $queue = [];

    protected $pid;

    public function addHandler($signal, callable $callback, $redefine = false)
    {
        $this->checkInstance();

        if (!isset($this->queue[$signal]) || $redefine) {
            $this->queue[$signal] = [];
            pcntl_signal($signal, [$this, 'handler']);
        }

        $this->queue[$signal][] = $callback;
    }

    public function removeHandler($signal, callable $callback)
    {
        $this->checkInstance();

        if (!isset($this->queue[$signal])) {
            return;
        }

        $founded = array_search($callback, $this->queue[$signal]);

        if (!$founded) {
            return;
        }

        unset($this->queue[$signal][$founded]);
    }

    public function handler($signal)
    {
        $this->checkInstance();

        if (!isset($this->queue[$signal])) {
            return;
        }

        foreach (array_reverse($this->queue[$signal]) as $handler) {
            call_user_func($handler, $signal);
        }
    }

    protected function checkInstance()
    {
        $currentPid = posix_getpid();
        if ($this->pid === $currentPid) {
            return;
        }

        $this->pid = $currentPid;
        $this->queue = [];
    }
}