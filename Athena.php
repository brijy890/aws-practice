<?php
require 'vendor/autoload.php';

use Aws\Athena\AthenaClient;

class CLSAthena {

    private $client;
    public function __construct()
    {
        $this->client = AthenaClient::factory([
            'region'  => 'ap-south-1',
            'version' => 'latest',
            'credentials' => [
                'key'    => 'AKIAJ7UOAG7JNQB2ECJA',
                'secret' => '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',
            ]
        ]);
    }

    public function listWorkGroups() {
    $result = $this->client->listWorkGroups([/* ... */]);
    return $result;
}

public function creteDatabase() {
    $result = $this->client->StartQueryExecution([
        'QueryString' => 'CREATE DATABASE php_test;',
        'ResultConfiguration' => [
    'OutputLocation' => 's3://brij890/', // REQUIRED
],
]);
}

}

$athenaClient = new CLSAthena();
print_r($athenaClient->listWorkGroups());
// print_r($athenaClient->creteDatabase());
