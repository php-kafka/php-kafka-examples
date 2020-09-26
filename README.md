# PHP Kafka Examples
This repository has PHP examples for Kafka consumers / producers for:
- [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka): Examples just using the PHP extension
- [php-kafka-lib](https://github.com/jobcloud/php-kafka-lib): PHP library that relies on [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) and supports [avro](https://github.com/flix-tech/avro-serde-php)

## Examples
- [php-rdkafka](src/ext-php-rdkafka/pure-php)
- [php-kafka-lib](src/ext-php-rdkafka/php-kafka-lib)

## Start containers for examples
Be sure to start the docker containers.  
To do so, run this in the project root:
```bash
docker-compose up -d
docker-compose exec php bash
```
Then follow the instructions in the example folders.

## Customize to fit your setup
If you wan't to test / debug something that is closer to your setup,  
you can modify the following arguments in `docker-compose.yml`:
```
PHP_IMAGE_TAG: 7.4-cli-alpine3.11
LIBRDKAFKA_VERSION: v1.4.0
PHP_RDKAFKA_VERSION: 4.0.3
```
Adjust those, to reflect your setup and afterwards run:
```
docker-compose up --build -d
```
