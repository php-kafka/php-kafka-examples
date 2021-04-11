# Running consumer / producer

## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose php exec -it bash
```

## Simple producer
Will per default produce 10 messages:
```bash
cd src/ext-php-simple-kafka-client/pure-php
php producer.php
```

## Transactional producer
Will per default produce 10 messages:
```bash
cd src/ext-php-simple-kafka-client/pure-php
php producer_transactional.php
```

## Consumer
Will consume all messages available:
```bash
cd src/ext-php-simple-kafka-client/pure-php
php consumer.php
```

## Query metadata
Will query metadata for all available topics, etc.:
```bash
cd src/ext-php-simple-kafka-client/pure-php
php metadata.php
```
