<?php

// Include the SDK using the composer autoloader
require 'vendor/autoload.php';

$s3 = new Aws\S3\S3Client([
    'region'  => 'ap-south-1',
    'version' => 'latest',
    'credentials' => [
        'key'    => 'xxxxxxxxxxxxxxx',
        'secret' => 'xxxxcccccccccccc',
    ]
]);

// Send a PutObject request and get the result object.
$key = 'SalesJan2009.csv';

$result = $s3->putObject([
    'Bucket' => 'brij890',
    'Key'    => $key,
    'Body'   => 'this is the body!',
    //'SourceFile' => 'c:\samplefile.png' -- use this if you want to upload a file from a local location
]);

// Print the body of the result by indexing into the result object.
var_dump($result);
