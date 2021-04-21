<?php

require_once('../vendor/autoload.php');

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Exception\SchemaNotFoundException;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use PhpKafka\Message\Decoder\AvroDecoder;
use PhpKafka\Message\Registry\AvroSchemaRegistry;
use PhpKafka\Message\KafkaAvroSchema;
use PhpKafka\Consumer\KafkaConsumerBuilder;
use PhpKafka\Exception\KafkaConsumerTimeoutException;
use PhpKafka\Exception\KafkaConsumerEndOfPartitionException;
use PhpKafka\Exception\KafkaConsumerConsumeException;

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
$schemaRegistry->addBodySchemaMappingForTopic(
    'php-kafka-lib-test-topic-avro',
    new KafkaAvroSchema(
        'nickzh.php.kafka.examples.entity.product-value'
        // optional param - version: if not passed we will take latest
    )
);
$schemaRegistry->addKeySchemaMappingForTopic(
    'php-kafka-lib-test-topic-avro',
    new KafkaAvroSchema(
        'nickzh.php.kafka.examples.entity.product-key'
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

            // Add additional output if you need to debug a problem
            // 'log_level' => (string) LOG_DEBUG,
            // 'debug' => 'all'
        ]
    )
    ->withAdditionalBroker('redpanda:9097')
    ->withConsumerGroup('php-kafka-lib-high-level-consumer-avro')
    ->withDecoder($decoder)
    ->withSubscription('php-kafka-lib-test-topic-avro')
    ->build();

$consumer->subscribe();

while (true) {
    try {
        $message = $consumer->consume(10000);
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
