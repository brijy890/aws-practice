<?php

// Include the SDK using the composer autoloader
require 'vendor/autoload.php';
$keys = parse_ini_file('keys.ini');
$s3 = new Aws\S3\S3Client([
            'region'  => $keys['region'],
            'version' => $keys['version'],
            'credentials' => [
                'key'    => $keys['key'],
                'secret' => $keys['secret'],
            ]
        ]);

// Send a PutObject request and get the result object.
$key = 'users/users.json';

$result = $s3->putObject([
    'Bucket' => 'brij890',
    'Key'    => $key,
    'SourceFile' => 'users.json'
    //'SourceFile' => 'c:\samplefile.png' -- use this if you want to upload a file from a local location
]);

// Print the body of the result by indexing into the result object.
var_dump($result);
