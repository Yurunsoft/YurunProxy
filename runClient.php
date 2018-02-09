<?php
namespace Yurun\Proxy;
require_once __DIR__ . '/vendor/autoload.php';
$client = new Client(include __DIR__ . '/config/client.php');
$client->start();
