# PHP Kafka Examples
This repository has PHP examples for Kafka consumers / producers for:
- [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka): Examples just using the PHP extension
- [php-kafka-lib](https://github.com/jobcloud/php-kafka-lib): PHP library that relies on [php-rdkafka](https://github.com/arnaud-lb/php-rdkafka) and supports [avro](https://github.com/flix-tech/avro-serde-php)
- [php-simple-kafka-client](https://github.com/php-kafka/php-simple-kafka-client): Examples just using the PHP extension
- [php-simple-kafka-lib](https://github.com/php-kafka/php-simple-kafka-lib): PHP library that relies on [php-simple-kafka-client](https://github.com/php-kafka/php-simple-kafka-client) and supports [avro](https://github.com/flix-tech/avro-serde-php)

## Examples
Checkout these folders to see how to run the examples:
- [php-rdkafka](src/ext-php-rdkafka)
- [php-simple-kafka-client](src/ext-php-simple-kafka-client)

## Examples with other compatible systems
- [Redpanda](src/redpanda) is a Kafka API compatible [streaming platform](https://vectorized.io/redpanda)

## Customize to fit your setup
If you wan't to test / debug something that is closer to your setup,  
you can modify the following arguments in `docker-compose.yml`:
```
PHP_IMAGE_TAG: 8.1-cli-alpine3.15
LIBRDKAFKA_VERSION: v1.8.2
PHP_EXTENSION: php-kafka/php-simple-kafka-client
PHP_EXTENSION_VERSION: v0.1.4
```
Adjust those, to reflect your setup and afterwards run:
```
docker-compose up --build -d
```
