<?php
require "vendor/autoload.php";
Predis\Autoloader::register();

$redis = new Predis\Client(array(
    "scheme" => "tcp",
    "host" => "localhost",
    "port" => 6379,
    "password” => “password"));
echo "Connected to Redis";
?>
