<?php

require_once('../../../../vendor/autoload.php');

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Consumer\KafkaConsumerBuilder;
use Jobcloud\Kafka\Exception\KafkaConsumerConsumeException;
use Jobcloud\Kafka\Exception\KafkaConsumerEndOfPartitionException;
use Jobcloud\Kafka\Exception\KafkaConsumerTimeoutException;
use Jobcloud\Kafka\Message\Decoder\AvroDecoder;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;

// Instantiate cached schema registry (vendor: flix)
$registry = new CachedRegistry(
    new BlockingRegistry(
        new PromisingRegistry(
            new Client(
                [
                    'base_uri' => 'http://kafka-schema-registry:9083',
                    //'auth' => ['user', 'pw']
                ]
            )
        )
    ),
    new AvroObjectCacheAdapter()
);

// Instantiate schema registry of lib (Note: In the future we will use our won cached registry)
$schemaRegistry = new AvroSchemaRegistry($registry);
// add schema for topic
$schemaRegistry->addSchemaMappingForTopic(
    'php-kafka-lib-test-topic-avro',
    new KafkaAvroSchema(
        'nickzh.php.kafka.examples.entity.product-value'
        // optional param - version: if not passed we will take latest
    )
);

// instantiate avro record serializer (vendor: flix)
$recordSerializer = new RecordSerializer($registry);

// initialize Avro decoder (Note: In the future, we will use our own record serializer)
$decoder = new AvroDecoder($schemaRegistry, $recordSerializer);

// Get consumer Builder instance
$builder = KafkaConsumerBuilder::create();

// Configure consumer
$consumer = $builder->withAdditionalConfig(
        [
            // start at the very beginning of the topic when reading for the first time
            'auto.offset.reset' => 'earliest',

            // will be visible in broker logs
            'client.id' => 'php-kafka-lib-high-level-consumer-avro',

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
    ->withConsumerGroup('php-kafka-lib-high-level-consumer-avro')
    ->withDecoder($decoder)
    ->withSubscription('php-kafka-lib-test-topic-avro')
    ->build();

$consumer->subscribe();

while (true) {
    try {
        $message = $consumer->consume();
    } catch (KafkaConsumerTimeoutException|KafkaConsumerEndOfPartitionException $e) {
        continue;
    } catch (KafkaConsumerConsumeException $e) {
        echo $e->getMessage() . PHP_EOL;
        continue;
    } catch (SchemaNotFoundException $e) {
        echo 'Consumed message with no or unknown schema' . PHP_EOL;
        continue;
    }

    echo sprintf(
        'Read message with key:%s payload:%s topic:%s partition:%d offset:%d headers:%s',
        $message->getKey(),
        implode(',', $message->getBody()),
        $message->getTopicName(),
        $message->getPartition(),
        $message->getOffset(),
        implode(',', $message->getHeaders())
    ) . PHP_EOL;

    $consumer->commit($message);
}
