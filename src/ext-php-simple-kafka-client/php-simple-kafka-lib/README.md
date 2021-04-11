# Register test schema
Run the following to register the test schema:
```bash
cd src/ext-php-simple-kafka-client/php-simple-kafka-lib
./console kafka-schema-registry:register:changed avroSchema
```

# Running consumer / producer
## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose php exec -it bash
```

## Avro producer
Will per default produce 10 avro messages:
```bash
php avroProducer.php
```

## Avro high level consumer
Will consume all messages available:
```bash
php avroConsumer.php
```

## Simple producer
Will per default produce 10 messages:
```bash
php producer.php
```

## Simple consumer
Will consume all messages available:
```bash
php consumer.php
```
