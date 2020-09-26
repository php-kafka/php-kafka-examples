<?php

require_once('../../../../vendor/autoload.php');

use FlixTech\AvroSerializer\Objects\RecordSerializer;
use FlixTech\SchemaRegistryApi\Registry\BlockingRegistry;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use GuzzleHttp\Client;
use Jobcloud\Kafka\Message\Encoder\AvroEncoder;
use Jobcloud\Kafka\Message\KafkaAvroSchema;
use Jobcloud\Kafka\Message\Registry\AvroSchemaRegistry;
use Jobcloud\Kafka\Producer\KafkaProducerBuilder;
use Jobcloud\Kafka\Message\KafkaProducerMessage;
use Ramsey\Uuid\Uuid;

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

// initialize Avro encode (Note: In the future, we will use our own record serializer)
$encoder = new AvroEncoder($schemaRegistry, $recordSerializer);

// Get producer Builder instance
$builder = KafkaProducerBuilder::create();

$producer = $builder->withAdditionalConfig(
    [
        // will be visible in broker logs
        'client.id' => 'php-kafka-lib-producer-avro',
        // set compression (supported are: none,gzip,lz4,snappy,zstd)
        'compression.codec' => 'snappy',
    ]
)
    ->withAdditionalBroker('kafka:9096')
    ->withEncoder($encoder)
    ->build();

for ($i = 0; $i < 10; ++$i) {
    $message = KafkaProducerMessage::create('php-kafka-lib-test-topic-avro', 0)
        ->withKey(sprintf('test-key-%d', $i))
        ->withBody(
            [
                'id' => Uuid::uuid6()->toString(),
                'name' => sprintf('Product %d', $i),
                'description' => 'A random test product',
                'price' => 21.25
            ]
        )
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
