<?php

namespace Kim1ne\Loop;

use Kim1ne\InputMessage;
use Kim1ne\Looper;
use React\EventLoop\Loop;

class Server
{
    private static bool $start = false;

    public static function run(Looper ...$loopers): void
    {
        if (self::isCli() === false) {
            throw new \Exception('Must be run from the command line.');
        }

        self::$start = true;
        $loop = Loop::get();

        foreach ($loopers as $stream) {
            $stream->setLoop($loop);
            $stream->run();
        }

        InputMessage::green('Start the Loop Server');

        register_shutdown_function(function ($loop) {
            $loop->stop();
        }, $loop);

        $loop->run();
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