<?php

namespace Kim1ne\Loop;

use Kim1ne\Core\Event;
use Kim1ne\Core\LooperInterface;

class EventEmitter
{
    public static function dispatch(string $eventName, Event $event): void
    {
        if (Server::isStart() === false) {
            throw new \Exception('The loop-server is not start.');
        }

        $workers = Server::getWorkers();

        foreach ($workers as $worker) {
            self::methodOfCallEvent($worker)->invoke($worker, $eventName, $event);
        }
    }

    private static function methodOfCallEvent(LooperInterface $worker): \ReflectionMethod
    {
        return new \ReflectionMethod($worker, 'call');
    }
}