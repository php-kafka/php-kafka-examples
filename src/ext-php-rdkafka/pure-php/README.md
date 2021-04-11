# Running consumer / producer

## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose php exec -it bash
```

## Producer
Will per default produce 10 messages:
```bash
cd src/ext-php-rdkafka/pure-php
php producer.php
```

## Consumer
Will consume all messages available:
```bash
cd src/ext-php-rdkafka/pure-php
php consumer.php
```
