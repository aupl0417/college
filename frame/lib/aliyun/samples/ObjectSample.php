<?php

require_once dirname(__FILE__).'/../aliyun.php';


use Aliyun\OSS\OSSClient;

// Sample of create client
function createClient($accessKeyId, $accessKeySecret) {
    return OSSClient::factory(array(
        'AccessKeyId' => $accessKeyId,
        'AccessKeySecret' => $accessKeySecret,
    ));
}

function listObjects(OSSClient $client, $bucket) {
    $result = $client->listObjects(array(
        'Bucket' => $bucket,
    ));
    foreach ($result->getObjectSummarys() as $summary) {
        echo 'Object key: ' . $summary->getKey() . "\n";
    }
}

// Sample of put object from string
function putStringObject(OSSClient $client, $bucket, $key, $content) {
    $result = $client->putObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
        'Content' => $content,
    ));
    echo 'Put object etag: ' . $result->getETag();
}

// Sample of put object from resource
function putResourceObject(OSSClient $client, $bucket, $key, $content, $size) {
    $result = $client->putObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
        'Content' => $content,
        'ContentLength' => $size,
    ));
    echo 'Put object etag: ' . $result->getETag();
}

// Sample of get object
function getObject(OSSClient $client, $bucket, $key) {
    $object = $client->getObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
    ));

    echo "Object: " . $object->getKey() . "\n";
    echo (string) $object;
}

// Sample of delete object
function deleteObject(OSSClient $client, $bucket, $key) {
    $client->deleteObject(array(
        'Bucket' => $bucket,
        'Key' => $key,
    ));
}

//$keyId = 'FM4LRK667sr2tBQD';

//$keySecret = 'HEgQxUgwSNZLt5GmzUsqsot0GBBhYE';

//$client = createClient($keyId, $keySecret);

//$bucket = 'yikuaiyou-image';

//$key = 'ad/test2.jpg';

//putStringObject($client, $bucket, $key, '123');

//putResourceObject($client, $bucket, $key, fopen(dirname(__FILE__).'/8043_06.jpg', 'r'), filesize(dirname(__FILE__).'/8043_06.jpg'));

//getObject($client, $bucket, $key);

//deleteObject($client, $bucket, $key);
