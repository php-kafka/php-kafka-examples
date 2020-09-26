<?php

use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\TopicConf;

error_reporting(E_ALL);

$conf = new Conf();
// will be visible in broker logs
$conf->set('client.id', 'pure-php-producer');
// set broker
$conf->set('metadata.broker.list', 'kafka:9096');
// set compression (supported are: none,gzip,lz4,snappy,zstd)
$conf->set('compression.codec', 'snappy');
// set timeout, producer will retry for 5s
$conf->set('message.timeout.ms', '5000');
//If you need to produce exactly once and want to keep the original produce order, uncomment the line below
//$conf->set('enable.idempotence', 'true');

// This callback processes the delivery reports from the broker
// you can see if your message was truly sent, this can be especially of importance if you poll async
$conf->setDrMsgCb(function (Producer $kafka, Message $message) {
    if ($message->err) {
        $errorStr = rd_kafka_err2str($message->err);

        echo sprintf('Message FAILED (%s, %s) to send with payload => %s', $message->err, $errorStr, $message->payload) . PHP_EOL;
    } else {
        // message successfully delivered
        echo sprintf('Message sent SUCCESSFULLY with payload => %s', $message->payload) . PHP_EOL;
    }
});

// SASL Authentication
// can be SASL_PLAINTEXT, SASL_SSL
// conf->set('security.protocol', '');
// can be GSSAPI, PLAIN, SCRAM-SHA-256, SCRAM-SHA-512, OAUTHBEARER
// $conf->set('sasl.mechanisms', '');
// $conf->set('sasl.username', '');
// $conf->set('sasl.password', '');
// default is none
// $conf->set('ssl.endpoint.identification.algorithm', 'https');


// SSL Authentication
//$conf->set('security.protocol', 'ssl');
//$conf->set('ssl.ca.location', __DIR__.'/../keys/ca.pem');
//$conf->set('ssl.certificate.location', __DIR__.'/../keys/kafka.cert');
//$conf->set('ssl.key.location', __DIR__.'/../keys/kafka.key');

$producer = new Producer($conf);
// initialize producer topic
$topic = $producer->newTopic('pure-php-test-topic');
// Produce 10 test messages
$amountTestMessages = 10;

// Loop to produce some test messages
for ($i = 0; $i < $amountTestMessages; ++$i) {
    // Let the partitioner decide the target partition, default partitioner is: RD_KAFKA_MSG_PARTITIONER_CONSISTENT_RANDOM
    // You can use a predefined partitioner or write own logic to decide the target partition
    $partition = RD_KAFKA_PARTITION_UA;

    //produce message with payload, key and headers
    $topic->producev(
        $partition,
        RD_KAFKA_MSG_F_BLOCK, // will block produce if queue is full
        sprintf('test message-%d',$i),
        sprintf('test-key-%d', $i),
        [
            'some' => sprintf('header value %d', $i)
        ]
    );
    echo sprintf('Queued message number: %d', $i) . PHP_EOL;

    // Poll for events e.g. producer callbacks, to handle errors, etc.
    // 0 = non-blocking
    // -1 = blocking
    // any other int value = timeout in ms
    $producer->poll(0);
}

// Shutdown producer, flush messages that are in queue. Give up after 20s
$result = $producer->flush(20000);

if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
    echo 'Was not able to shutdown within 20s. Messages might be lost!' . PHP_EOL;
}
