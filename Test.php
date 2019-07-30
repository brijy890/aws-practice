<?php
require 'vendor/autoload.php';

use Aws\Athena\AthenaClient;

class CLSAthena {

    private $client;
    // public function __construct()
    // {
    //     $keys = parse_ini_file('keys.ini');
    //     $this->client = AthenaClient::factory([
    //         'region'  => $keys['region'],
    //         'version' => $keys['version'],
    //         'credentials' => [
    //             'key'    => $keys['key'],
    //             'secret' => $keys['secret'],
    //         ]
    //     ]);
    // }

public function __construct()
{
    $keys = parse_ini_file('keys.ini');
    try {
        $this->client = AthenaClient::factory(array(
            'version' => $keys['version'],
            'region' => $keys['region'],
            'credentials' => array(
                'key' => $keys['key'],
                'secret' => $keys['secret'],
        )
        ));
    }
    catch (AthenaException $e) {
        // Catch an S3 specific exception.
        echo $e->getMessage();
    } catch (AwsException $e) {
        // This catches the more generic AwsException. You can grab information
        // from the exception using methods of the exception object.
        echo $e->getAwsRequestId() . "\n";
        echo $e->getAwsErrorType() . "\n";
        echo $e->getAwsErrorCode() . "\n";
    }
}

public function query($params = array()) {
    $query = "SELECT ";
    $query .= (isset($params['column']) && !empty($params['column'])) ? $params['column'] : "*";
    $query .= " FROM ".$params['tb_name'];


    $result = $this->client->StartQueryExecution([
        'QueryString' => $query,
        'QueryExecutionContext' => [
                'Database' => $params['db_name'],
            ],
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
$query['column'] = "name";
$query['tb_name'] = "users";
$query['db_name'] = "athena";
$result = $athenaClient->query($query);
// // $result = $athenaClient->createNamedQuery();
print_r($result);
