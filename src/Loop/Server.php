<?php

namespace Kim1ne\Loop;

use Kim1ne\InputMessage;
use Kim1ne\Looper;
use React\EventLoop\Loop;

class Server
{
    private static bool $start = false;
    private static array $hash2worker = [];

    public static function run(Looper ...$loopers): void
    {
        if (self::$start) {
            return;
        }

        if (self::isCli() === false) {
            throw new \Exception('Must be run from the command line.');
        }

        self::$start = true;
        $loop = Loop::get();

        foreach ($loopers as $stream) {
            self::add($stream);

            $stream->setLoop($loop);
            $stream->run();
        }

        InputMessage::green('Start the Loop Server');

        register_shutdown_function(function ($loop) {
            $loop->stop();
        }, $loop);

        $loop->run();
    }

    private static function add(Looper $worker): void
    {
        self::$hash2worker[self::getHashWorker($worker)] = $worker;
    }

    private static function getHashWorker(Looper $worker): string
    {
        return spl_object_hash($worker);
    }

    public static function destroy(Looper $worker): void
    {
        if (self::$start === false) {
            return;
        }

        $hash = self::getHashWorker($worker);

        if (!isset(self::$hash2worker[$hash])) {
            return;
        }

        $worker = self::$hash2worker[$hash];

        $worker->stop();
        unset(self::$hash2worker[$hash]);

        if (empty(self::$hash2worker)) {
            Loop::get()->stop();
        }
    }

    public static function stop(): void
    {
        if (self::$start === false) {
            return;
        }

        foreach (self::$hash2worker as $worker) {
            self::destroy($worker);
        }
    }

    private static function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    public static function isStart(): bool
    {
        return self::$start;
    }
}