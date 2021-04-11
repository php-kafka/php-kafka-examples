# Register test schema
Run the following to register the test schema:
```bash
cd php-kafka-lib
./console kafka-schema-registry:register:changed avroSchema
```

# Running consumer / producer
## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose exec php bash
```

## Avro producer
Will per default produce 10 avro messages:
```bash
php avroProducer.php
```

## Avro consumer
Will consume all messages available:
```bash
php avroConsumer.php
```

## Producer
Will per default produce 10 messages:
```bash
php producer.php
```

## Consumer
Will consume all messages available:
```bash
php consumer.php
```
