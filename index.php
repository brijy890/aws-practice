<?php
require 'vendor/autoload.php';
$client = Elasticsearch\ClientBuilder::create()->build();
 
$params = [
 'index' => 'my_index',
 'type' => 'my_type',
 'id' => 'my_id',
];
 
$response = $client->get($params);
echo $response['_source']['first field'];
