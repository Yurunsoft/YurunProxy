<?php
namespace Yurun\Proxy;
require_once __DIR__ . '/vendor/autoload.php';
$server = new Server(include __DIR__ . '/config/server.php');
$server->start();
