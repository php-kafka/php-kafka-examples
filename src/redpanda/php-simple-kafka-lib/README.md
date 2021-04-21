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
php producer.php
```

## Simple consumer
Will consume all messages available:
```bash
php consumer.php
```
