<?php

// DISCLAIMER: For most intent and purposes it is easier and more flexible to use the high level consumer

use RdKafka\Conf;
use RdKafka\Consumer;
use RdKafka\TopicConf;

$conf = new Conf();
// will be visible in broker logs
$conf->set('client.id', 'pure-php-low-level-consumer');
// set consumer group, e.g. <my-application-name>-consumer
$conf->set('group.id', 'pure-php-low-level-consumer');
// set broker
$conf->set('metadata.broker.list', 'kafka:9096');

// don't auto commit, give the application the control to do that (default is: true)
// this will give you control of the local offset store, which will be auto committed.
// Low level consumer works a bit differently in this case
$conf->set('enable.auto.offset.store', 'false');

// Get eof code instead of null
$conf->set('enable.partition.eof', 'true');

// SASL Authentication
//$conf->set('sasl.mechanisms', '');
//$conf->set('ssl.endpoint.identification.algorithm', 'https');
//$conf->set('sasl.username', '');
//$conf->set('sasl.password', '');

// SSL Authentication
//$conf->set('security.protocol', 'ssl');
//$conf->set('ssl.ca.location', __DIR__.'/../keys/ca.pem');
//$conf->set('ssl.certificate.location', __DIR__.'/../keys/kafka.cert');
//$conf->set('ssl.key.location', __DIR__.'/../keys/kafka.key');

$consumer = new Consumer($conf);

// new topic configuration
$topicConf = new TopicConf();
// Once we have stored the offset, it should be auto commited quickly to the broker
$topicConf->set('auto.commit.interval.ms', '100');
// start at the very beginning of the topic when reading for the first time
$topicConf->set('auto.offset.reset', 'earliest');

// get consumer topic
$topic = $consumer->newTopic('pure-php-test-topic', $topicConf);
// initialize consumption, continue from offset (or auto.offset.reset)
$topic->consumeStart(0, RD_KAFKA_OFFSET_STORED);

while (true) {
    // Try to consume messages for the given timout (20s)
    $message = $topic->consume(0, 20000);

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

    // Commit message to local offset store (which will be auto commited to broker)
    $topic->offsetStore($message->partition, $message->offset);
}
