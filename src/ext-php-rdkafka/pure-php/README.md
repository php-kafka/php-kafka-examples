# Running consumer / producer

## Prerequisites
Be sure to do this first: [Start containers](./../../../README.md#start-containers-for-examples)

## Producer
Will per default produce 10 messages:
```bash
cd src/ext-php-rdkafka/pure-php
php producer.php
```

## Low level consumer
Will consume all messages available for partition 0:
```bash
cd src/ext-php-rdkafka/pure-php
php lowLevelConsumer.php
```

## High level consumer
Will consume all messages available:
```bash
cd src/ext-php-rdkafka/pure-php
php highLevelConsumer.php
```
