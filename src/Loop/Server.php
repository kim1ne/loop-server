<?php

namespace Kim1ne\Loop;

use Kim1ne\Core\InputMessage;
use Kim1ne\Core\LooperInterface;
use React\EventLoop\Loop;

class Server
{
    private static bool $start = false;
    private static array $hash2worker = [];

    public static function run(LooperInterface ...$loopers): void
    {
        if (self::$start) {
            return;
        }

        if (self::isCli() === false) {
            throw new \Exception('Must be run from the command line.');
        }

        InputMessage::green('The Loop Server started.');

        self::$start = true;
        $loop = Loop::get();

        foreach ($loopers as $stream) {
            self::add($stream);

            $stream->setLoop($loop);
            $stream->run();
        }

        register_shutdown_function(function ($loop) {
            $loop->stop();
        }, $loop);

        $loop->run();
    }

    /**
     * @return LooperInterface[]
     */
    public static function getWorkers(): array
    {
        if (self::$start === false) {
            return [];
        }

        return self::$hash2worker;
    }

    private static function add(LooperInterface $worker): void
    {
        self::$hash2worker[self::getHashWorker($worker)] = $worker;
    }

    private static function getHashWorker(LooperInterface $worker): string
    {
        return spl_object_hash($worker);
    }

    public static function destroy(LooperInterface $worker): void
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

        InputMessage::green($worker->getScopeName() . ' - stopped.');

        if (empty(self::$hash2worker)) {
            Loop::get()->stop();

            InputMessage::green('The Loop Server stopped.');
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