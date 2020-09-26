<?php

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

$conf = new Conf();
// will be visible in broker logs
$conf->set('client.id', 'pure-php-high-level-consumer');
// set consumer group, e.g. <my-application-name>-consumer
$conf->set('group.id', 'pure-php-high-level-consumer');
// set broker
$conf->set('metadata.broker.list', 'kafka:9096');
// don't auto commit, give the application the control to do that (default is: true)
$conf->set('enable.auto.commit', 'false');
// start at the very beginning of the topic when reading for the first time
$conf->set('auto.offset.reset', 'earliest');
// Get eof code instead of null
$conf->set('enable.partition.eof', 'true');

// SASL Authentication
//$conf->set('sasl.mechanisms', '');
//$conf->set('ssl.endpoint.identification.algorithm', 'https');
//$conf->set('sasl.username', '');
//$conf->set('sasl.password', '');

// SSL Authentication
//$conf->set('security.protocol', 'ssl');
//$conf->set('ssl.ca.location', __DIR__.'/../../../keys/ca.pem');
//$conf->set('ssl.certificate.location', __DIR__.'/../../../keys/kafka.cert');
//$conf->set('ssl.key.location', __DIR__.'/../../../keys/kafka.key');

// Create high level consumer
$consumer = new KafkaConsumer($conf);

// Subscribe to one or multiple topics
$consumer->subscribe(['pure-php-test-topic']);

while (true) {
    // Try to consume messages for the given timout (20s)
    $message = $consumer->consume(20000);

    if (RD_KAFKA_RESP_ERR__PARTITION_EOF === $message->err) {
        echo 'Reached end of partition, waiting for more messages...' . PHP_EOL;
        continue;
    } else if (RD_KAFKA_RESP_ERR__TIMED_OUT === $message->err) {
        echo 'Timed out without receiving a new message, waiting for more messages...' . PHP_EOL;
        continue;
    } else if (RD_KAFKA_RESP_ERR_NO_ERROR !== $message->err) {
        echo rd_kafka_err2str($message->err) . PHP_EOL;
        continue;
    }

    echo sprintf(
        'Read message with key:%s payload:%s topic:%s partition:%d offset:%d',
        $message->key,
        $message->payload,
        $message->topic_name,
        $message->partition,
        $message->offset
    ) . PHP_EOL;
    // Here is where you do your business logic to process your message
    // after you have done so, commit the message offset to the broker

    // commit the message(s) offset synchronous back to the broker
    $consumer->commit($message);

    // you can also commit the message(s) offset in an async manner, which is slightly faster
    // but poses of course the challenge of handling errors in an async manner as well
    //$consumer->commitAsync($message);
}
