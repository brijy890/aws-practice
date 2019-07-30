<?php
require 'vendor/autoload.php';

use Aws\Athena\AthenaClient;
use Aws\S3\S3Client;

class Athena {
	public $Client = "";

	function __construct() {
		$keys = parse_ini_file('keys.ini');
		try {
			$this->Client = AthenaClient::factory(array(
				'version' => $keys['version'],
				'region' => $keys['region'],
				'credentials' => array(
					'key' => $keys['key'],
					'secret' => $keys['secret'],
				),
			));
		} catch (AthenaException $e) {
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

	function getData($db_name, $table_name, $query = "", $result_logs = "") {
		try {
			$result = $this->Client->StartQueryExecution(
				array(
					"QueryExecutionContext" => array("Database" => $db_name),
					"QueryString" => $query,
				)
			);

			$QueryExecutionId = $result->get('QueryExecutionId');

			$this->waitForQueryToComplete($QueryExecutionId);

			$result = $this->Client->GetQueryResults(array(
				'QueryExecutionId' => $QueryExecutionId, // REQUIRED
				'MaxResults' => 1,
			));

			$data = $result->get('ResultSet');
			$res = $data['Rows'];

			while (true) {

				if ($result->get('NextToken') == null) {
					break;
				}

				$result = $this->Client->GetQueryResults(array(
					'QueryExecutionId' => $QueryExecutionId, // REQUIRED
					'NextToken' => $result->get('NextToken'),
					'MaxResults' => 1,
				));

				$data = $result->get('ResultSet');
				$res = array_merge($res, $data['Rows']);
			}
			$keys = parse_ini_file('keys.ini');
			$s3 = new S3Client([
				'version' => $keys['version'],
				'region'  => $keys['region'],
				'credentials' => array(
					'key' => $keys['key'],
					'secret' => $keys['secret'],
				),
			]);

			try {
			// Get the object.
			$result = $s3->getObject([
				'Bucket' => "brij890",
				'Key'    => $QueryExecutionId.".csv",
				'SaveAs' => "/var/www/html/elasticsearch/".$QueryExecutionId.".csv"
			]);

			$result = $s3->deleteObjects([
				'Bucket' => 'brij890', // REQUIRED
				'Delete' => [ // REQUIRED
					'Objects' => [ // REQUIRED
						[
							'Key' => $QueryExecutionId.".csv", // REQUIRED
						],
						[
							'Key' => $QueryExecutionId.".csv.metadata", // REQUIRED
						],
					]
				]
			]);

			// Display the object in the browser.
			header("Content-Type: {$result['ContentType']}");
			echo $result['Body'];
			} catch (S3Exception $e) {
			echo $e->getMessage() . PHP_EOL;
			}

			// $resData = $this->processResultRows($res);
			// return $resData;
		} catch (AthenaException $e) {
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

/*
 * function to wait for fetch result from query
 */
	function waitForQueryToComplete($QueryExecutionId) {
		while (1) {
			$result = $this->Client->getQueryExecution(array('QueryExecutionId' => $QueryExecutionId));
			$res = $result->toArray();

			//echo $res['QueryExecution']['Status']['State'].'<br/>';
			if ($res['QueryExecution']['Status']['State'] == 'FAILED') {
				echo 'Query Failed';
				die;
			} else if ($res['QueryExecution']['Status']['State'] == 'CANCELED') {
				echo 'Query was cancelled';
				die;
			} else if ($res['QueryExecution']['Status']['State'] == 'SUCCEEDED') {
				break; // break while loop
			}

		}
	}

/*
 * function to process data
 */
	function processResultRows($res) {
		$result = array();
		$result_array = array();

		// echo '@@@Count: '.count($res).'<br/>';

		for ($i = 0; $i <= count($res); $i++) {
			for ($n = 0; $n < count($res[$i]['Data']); $n++) {
				if ($i == 0) {
					$result[] = $res[$i]['Data'][$n]['VarCharValue'];
				} else {
					$result_array[$i][$result[$n]] = $res[$i]['Data'][$n]['VarCharValue'];
				}
			}
		}

		// echo 'result_array_cnt: '.count($result_array).'<br/>';
		print_r($result_array);die;
		return $result_array;
	}
}

$athenaClient = new Athena();
$result = $athenaClient->getData("athena", "users", "SELECT * FROM users", "brij890");
print_r($result);
