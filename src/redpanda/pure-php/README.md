# Running consumer / producer

## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose exec php bash
```

## Simple producer
Will per default produce 10 messages:
```bash
cd pure-php
php producer.php
```

## Transactional producer (currently unsupported by Redpanda)
Will per default produce 10 messages:
```bash
cd pure-php
php producer_transactional.php
```

## Consumer
Will consume all messages available:
```bash
cd pure-php
php consumer.php
```

## Query metadata
Will query metadata for all available topics, etc.:
```bash
cd pure-php
php metadata.php
```
