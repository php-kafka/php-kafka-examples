<?php

// DISCLAIMER: this feature is not released yet and is subject to change

use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer;
use RdKafka\TopicConf;
use RdKafka\KafkaErrorException;

error_reporting(E_ALL);

// -------- Intro --------
// The transactional producer operates on top of the idempotent producer,
// and provides full exactly-once semantics (EOS) for Apache Kafka when used
// with the transaction aware consumer (isolation.level=read_committed, which is the default).

// function to handle output of transaction error
function echoTransactionError(KafkaErrorException $e) {
    $retryString = 'is not retriable';
    $fatalString = 'is not fatal';
    $abortString = 'doesn\'t need transaction abort';

    if ($e->isFatal()) {
        $fatalString = 'is fatal';
    }

    if ($e->isRetriable()) {
        $retryString = 'is retriable';
    }

    if ($e->transactionRequiresAbort()) {
        $abortString = 'needs transaction abort';
    }

    echo 'Was unable to initialize the transactional producer' . PHP_EOL;

    echo sprintf('The reason was: %s, this error %d, %s, %s, %s', $e->getMessage(), $e->getCode(), $fatalString, $retryString, $abortString) . PHP_EOL;
    echo sprintf('In detail this means %s', $e->getErrorString()) . PHP_EOL;
    echo sprintf('Trace is %s', $e->getTraceAsString()) . PHP_EOL;
}

$conf = new Conf();
// will be visible in broker logs
$conf->set('client.id', 'pure-php-producer');
// set broker
$conf->set('metadata.broker.list', 'kafka:9096');
// set compression (supported are: none,gzip,lz4,snappy,zstd)
$conf->set('compression.codec', 'snappy');
// set timeout, producer will retry for 5s
$conf->set('message.timeout.ms', '5000');

// For the transactional producer you need a unique id to identify it
$conf->set('transactional.id', 'some-unique-id-of-your-producer-to-recognize-it');

// This callback processes the delivery reports from the broker
// you can see if your message was truly sent
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
//$conf->set('security.protocol', '');
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
$topic = $producer->newTopic('pure-php-transactional-test-topic');
// Produce 10 test messages
$amountTestMessages = 10;

// Initialize transactions
try {
    $producer->initTransactions(10000);
} catch (KafkaErrorException $e) {
    echoTransactionError($e);
    die;
}

// Begin transaction for our 10 messages
try {
    $producer->beginTransaction();
} catch (KafkaErrorException $e) {
    echoTransactionError($e);
    die;
}

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

// Commit transaction for our 10 messages
try {
    $producer->commitTransaction(10000);
} catch (KafkaErrorException $e) {
    echoTransactionError($e);
    die;
}

// Shutdown producer, flush messages that are in queue. Give up after 20s
$result = $producer->flush(20000);

if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
    echo 'Was not able to shutdown within 20s. Messages might be lost!' . PHP_EOL;
}
