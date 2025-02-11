# Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require kim1ne/loop-server
```

This component unites all libraries, where needs start an infinite process. Example:
 ***
Producers publishing messages to the kafka. Workers expect messages from the kafka and when got messages, sending to the socket server. The socket-server sending messages to all connected clients
![image](https://github.com/user-attachments/assets/5657a5c2-7583-492c-ad05-bf16f2aeac2a)
```php
use RdKafka\Conf;
use Kim1ne\Socket\Server\Server;
use Kim1ne\Socket\Server\Connection;
use Kim1ne\Socket\Server\Message;
use Kim1ne\Socket\Server\Transport;
use Kim1ne\Kafka\KafkaWorker;
use Kim1ne\Kafka\KafkaConsumer;
use Kim1ne\Kafka\Message as KafkaMessage;

$server = $server = new Server(transport: Transport::WS);

$server->on('message', function (Message $message, Connection $connection, Server $server) {
    InputMessage::green("I've got the message!");
});

$conf = new Conf(...);

$worker = new KafkaWorker($conf);
$worker->subscribe(['my-topic'])

$worker->on(function (KafkaMessage $message, KafkaConsumer $consumer) use ($server) {
    $consumer->commitAsync($message);
    $server->sendAll($message->payload);
});

\Kim1ne\Loop\Server::run($server, $worker);
```

<details>
    <summary>Version 1.1.0</summary>

- All components have been updated 1.1.0
- A component has a scope-name. It has the method - getScopeName() - returns the scope-name. All events, which sends a component will merge with the scope-name.
- All components are isolated from each other. A component can sends an event, and another component will waiting for the event
----
Example:
the object [KafkaWorker](https://github.com/kim1ne/kim1ne-kafka/blob/main/src/Kafka/KafkaWorker.php) has scope-name - `kafka:worker`, and sends an event `message`, the event will called `kafka:worker:message`

```php
use Kim1ne\Socket\Server\Server;
use Kim1ne\Kafka\KafkaWorker;
use Kim1ne\Kafka\Message;
use Kim1ne\Kafka\KafkaConsumer;
use Kim1ne\Core\Event;

/**
 * @var Server $server
 */
$server->on('kafka:worker:message', function (Event $event) use ($server) {
    $message = $event->get('message');
    $server->sendAll($message);
});

/**
 * @var KafkaWorker $worker
 */
$worker->on('message', function (Message $message, KafkaConsumer $consumer) use ($worker) {
    $worker->dispatchEvent('message', new Event([
        'message' => $message->payload
    ]));
});
```
</details>
