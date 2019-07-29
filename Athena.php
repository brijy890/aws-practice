<?php
require 'vendor/autoload.php';

use Aws\Athena\AthenaClient;

class CLSAthena {

    private $client;
    public function __construct()
    {
        $keys = parse_ini_file('keys.ini');
        $this->client = AthenaClient::factory([
            'region'  => $keys['region'],
            'version' => $keys['version'],
            'credentials' => [
                'key'    => $keys['key'],
                'secret' => $keys['secret'],
            ]
        ]);
    }

    public function listWorkGroups() {
        $result = $this->client->listWorkGroups([/* ... */]);
        return $result;
    }

public function query($query) {
    $result = $this->client->StartQueryExecution([
        'QueryString' => $query,
        'ResultConfiguration' => [
            'OutputLocation' => 's3://brij890/', // REQUIRED
        ],
    ]);
    $QueryExecutionId = $result->get('QueryExecutionId');
    $this->waitForQueryToComplete($QueryExecutionId);
    return $this->result($result);
}

public function result($result) {
    $res = $this->client->getQueryResults([
        "QueryExecutionId" => $result['QueryExecutionId']
    ]);
    print_r($res);die;
    return $res;
}

public function waitForQueryToComplete($QueryExecutionId)
{
    while(1)
    {
        $result = $this->client->getQueryExecution(array('QueryExecutionId' => $QueryExecutionId));
        $res = $result->toArray();

        //echo $res['QueryExecution']['Status']['State'].'<br/>';
        if($res['QueryExecution']['Status']['State']=='FAILED')
        {
            echo 'Query Failed';
            die;
        }
        else if($res['QueryExecution']['Status']['State']=='CANCELED')
        {
            echo 'Query was cancelled';
            die;
        }
        else if($res['QueryExecution']['Status']['State']=='SUCCEEDED')
        {
            break; // break while loop
        }
    }
}

}

$athenaClient = new CLSAthena();
// print_r($athenaClient->listWorkGroups());
$query = "SELECT * FROM sampledb.elb_logs limit 1";
$result = $athenaClient->query($query);
print_r($result);
