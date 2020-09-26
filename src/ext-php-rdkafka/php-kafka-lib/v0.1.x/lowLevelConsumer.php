<?php

// DISCLAIMER: For most intent and purposes it is easier and more flexible to use the high level consumer

require_once('../../../../vendor/autoload.php');

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

        // control how fast a commited offset will be synced to the broker
        //'auto.commit.interval.ms' => 100,
        // will be visible in broker logs
        'client.id' => 'php-kafka-lib-low-level-consumer',

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
    ->withTimeout(10000)
    ->withConsumerGroup('php-kafka-lib-low-level-consumer')
    ->withConsumerType(KafkaConsumerBuilder::CONSUMER_TYPE_LOW_LEVEL)
    ->withSubscription(
        'php-kafka-lib-test-topic'
        // optional param - partitions: if none are given, we will query the topic and subscribe to all partitions, like the high level consumer
    )
    ->build();

$consumer->subscribe();

while (true) {
    try {
        $message = $consumer->consume();
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
