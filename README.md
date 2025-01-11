# Installation

This package can be installed as a [Composer](https://getcomposer.org/) dependency.

```bash
composer require kim1ne/loop-server
```

This component unites all libraries, where needs start an infinite process. Example: creation loop-server with the [kafka worker](https://github.com/kim1ne/kim1ne-kafka) and the [socket server](https://github.com/kim1ne/socket-server):
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