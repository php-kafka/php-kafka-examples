<?php

require_once('../../../../vendor/autoload.php');

use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Ramsey\Uuid\Uuid;

// Get producer Builder instance
$builder = KafkaProducerBuilder::create();

$producer = $builder->withAdditionalConfig(
    [
        // will be visible in broker logs
        'client.id' => 'php-kafka-lib-producer',
        // set compression (supported are: none,gzip,lz4,snappy,zstd)
        'compression.codec' => 'snappy',
    ]
)
    ->withAdditionalBroker('kafka:9096')
    ->build();

for ($i = 0; $i < 10; ++$i) {
    $message = KafkaProducerMessage::create('php-kafka-lib-test-topic', 0)
        ->withKey(sprintf('test-key-%d', $i))
        ->withBody(sprintf('test message-%d',$i))
        ->withHeaders(
            [
                'some' => 'test header'
            ]
        );

    $producer->produce($message);
    echo sprintf('Queued message number: %d', $i) . PHP_EOL;
}

// Shutdown producer, flush messages that are in queue. Give up after 20s
$result = $producer->flush(20000);

if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
    echo 'Was not able to shutdown within 20s. Messages might be lost!' . PHP_EOL;
}
