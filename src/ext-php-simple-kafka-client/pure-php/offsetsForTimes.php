<?php

use SimpleKafkaClient\Configuration;
use SimpleKafkaClient\Message;
use SimpleKafkaClient\Consumer;
use SimpleKafkaClient\Producer;
use SimpleKafkaClient\TopicPartition;

error_reporting(E_ALL);

$conf = new Configuration();
$conf->set('client.id', 'pure-php-producer');
$conf->set('metadata.broker.list', 'kafka:9096');
$conf->set('compression.codec', 'snappy');
$conf->set('message.timeout.ms', '5000');

$producer = new Producer($conf);
$topic = $producer->getTopicHandle('pure-php-test-topic-offsets');
$time = time();
$topic->producev(
    RD_KAFKA_PARTITION_UA,
    RD_KAFKA_MSG_F_BLOCK, // will block produce if queue is full
    'special-message',
    'special-key',
    [
        'special-header' => 'awesome'
    ]
);
$result = $producer->flush(20000);

$topicPartition = new TopicPartition('pure-php-test-topic-offsets', 0, $time);
$result = $producer->offsetsForTimes([$topicPartition], 10000);
var_dump($result[0]->getTopicName());
var_dump($result[0]->getPartition());
var_dump($result[0]->getOffset());

