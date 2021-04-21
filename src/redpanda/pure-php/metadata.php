<?php

use SimpleKafkaClient\Configuration;
use SimpleKafkaClient\Producer;

error_reporting(E_ALL);

$conf = new Configuration();
// will be visible in broker logs
$conf->set('client.id', 'pure-php-producer');
// set broker
$conf->set('metadata.broker.list', 'redpanda:9097');
// set compression (supported are: none,gzip,lz4,snappy,zstd)

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

// Add additional output if you need to debug a problem
// $conf->set('log_level', (string) LOG_DEBUG);
// $conf->set('debug', 'all');

$producer = new Producer($conf);

// get metadata
$metadata = $producer->getMetadata(false, 10000);
echo sprintf('Broker id: %d', $metadata->getOrigBrokerId()) . PHP_EOL;
echo sprintf('Broker name: %s', $metadata->getOrigBrokerName()) . PHP_EOL;

echo 'Info about full broker list' . PHP_EOL;
$brokers = $metadata->getBrokers();
while ($brokers->valid()) {
    echo sprintf('Broker id: %d', $brokers->current()->getId()) . PHP_EOL;
    echo sprintf('Broker host: %s', $brokers->current()->getHost()) . PHP_EOL;
    echo sprintf('Broker port: %d', $brokers->current()->getPort()) . PHP_EOL;
    $brokers->next();
}

echo 'Info about topics' . PHP_EOL;
$topics = $metadata->getTopics();
while ($topics->valid()) {
    echo sprintf('Topic name: %s', $topics->current()->getName()) . PHP_EOL;
    echo sprintf('Topic error: %d', $topics->current()->getErrorCode()) . PHP_EOL;
    $partitions = $topics->current()->getPartitions();
    while ($partitions->valid()) {
        echo sprintf('  Topic partition id: %d', $partitions->current()->getId()) . PHP_EOL;
        echo sprintf('  Topic partition err: %d', $partitions->current()->getErrorCode()) . PHP_EOL;
        echo sprintf('  Topic partition leader id: %d', $partitions->current()->getLeader()) . PHP_EOL;
        $replicas = $partitions->current()->getReplicas();
        while ($replicas->valid()) {
            echo sprintf('    Replicas id: %d', $replicas->current()) . PHP_EOL;
            $replicas->next();
        }
        $inSyncReplicas = $partitions->current()->getIsrs();
        while ($inSyncReplicas->valid()) {
            echo sprintf('    Insync Replicas id: %d', $inSyncReplicas->current()) . PHP_EOL;
            $inSyncReplicas->next();
        }
        $partitions->next();
    }

    $topics->next();
}