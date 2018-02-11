<?php
namespace Yurun\Proxy;

use \Yurun\Proxy\Consts\ServerAction;
use Yurun\Util\HttpRequestMultipartBody;

class ClientParser
{
	public $socket;

	public $worker;

	public function __construct($socket)
	{
		$this->socket = $socket;
		$this->worker = new ClientWorker($socket);
	}

	public function init()
	{
		$this->worker->init();
	}

	public function receive($data)
	{
		$data = $this->parseData($data);
		switch($data['a'])
		{
			case ServerAction::RECEIVE_HTTP_REQUEST:
				$this->worker->receiveHttpRequest($data['data']);
				break;
			case ServerAction::INIT_RESPONSE:
				$this->worker->initResponse($data['data']);
				break;
		}
	}

	public function parseData($data)
	{
		return json_decode(substr($data, 0, -2), true);
	}
}