# Running consumer / producer

## Prerequisites
Be sure to do this first: [Start containers](./../README.md)
Connect to the php container:
```bash
docker-compose exec php bash
```

## Producer
Will per default produce 10 messages:
```bash
cd pure-php
php producer.php
```

## Consumer
Will consume all messages available:
```bash
cd pure-php
php consumer.php
```
