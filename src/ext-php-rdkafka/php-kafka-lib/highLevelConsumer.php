<?php

require_once('../../../vendor/autoload.php');

use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;

// Get consumer Builder instance
$builder = KafkaConsumerBuilder::create();

// Configure consumer
$consumer = $builder->withAdditionalConfig(
    [
        // start at the very beginning of the topic when reading for the first time
        'auto.offset.reset' => 'earliest',

        // will be visible in broker logs
        'client.id' => 'php-kafka-lib-high-level-consumer',

        // SSL settings
        //'security.protocol' => 'ssl',
        //'ssl.ca.location' => __DIR__.'/../../../keys/ca.pem',
        //'ssl.certificate.location' => __DIR__.'/../../../keys/apl_stage.cert',
        //'ssl.key.location' => __DIR__.'/../../../keys/apl_stage.key',

        // SASL settings
        //'sasl.mechanisms' => '',
        //'ssl.endpoint.identification.algorithm' => 'https',
        //'sasl.username' => '',
        //'sasl.password' => '',
    ]
)
    ->withAdditionalBroker('kafka:9096')
    ->withConsumerGroup('php-kafka-lib-high-level-consumer')
    ->withSubscription('php-kafka-lib-test-topic')
    ->build();

$consumer->subscribe();

while (true) {
    try {
        $message = $consumer->consume(10000);
    } catch (KafkaConsumerTimeoutException|KafkaConsumerEndOfPartitionException $e) {
        echo 'Didn\'t receive any messages, waiting for more...' . PHP_EOL;
        continue;
    } catch (KafkaConsumerConsumeException $e) {
        echo $e->getMessage() . PHP_EOL;
        continue;
    }

    echo sprintf(
            'Read message with key:%s payload:%s topic:%s partition:%d offset:%d headers:%s',
            $message->getKey(),
            $message->getBody(),
            $message->getTopicName(),
            $message->getPartition(),
            $message->getOffset(),
            implode(',', $message->getHeaders())
        ) . PHP_EOL;

    $consumer->commit($message);
}
