# Register test schema
Run the following to register the test schema:
```bash
cd src/ext-php-rdkafka/php-kafka-lib
./console kafka-schema-registry:register:changed avroSchema
```

# Running consumer / producer
## Prerequisites
Be sure to do this first: [Start containers](./../../../README.md#start-containers-for-examples)
and to also have registered the test schema (see above).

## Avro producer
Will per default produce 10 avro messages:
```bash
php avroProducer.php
```

## Avro high level consumer
Will consume all messages available:
```bash
php avroHighLevelConsumer.php
```

## Producer
Will per default produce 10 messages:
```bash
php producer.php
```

## Low level consumer
Will consume all messages available for partition 0:
```bash
php lowLevelConsumer.php
```

## High level consumer
Will consume all messages available:
```bash
php highLevelConsumer.php
```
