<?php
namespace Yurun\Proxy;

class Client
{
	private $option;

	private $parser;

	public function __construct($option)
	{
		$this->option = $option;
	}

	public function start()
	{
		$socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(false === $socket)
		{
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . PHP_EOL;
			exit;
		}
		$result = socket_connect($socket, $this->option['server']['ip'], $this->option['server']['port']);
		if(!$result)
		{
			echo "socket_connect() failed: reason: " . socket_strerror(socket_last_error()) . PHP_EOL;
			exit;
		}
		$this->parser = new ClientParser($socket, $this->option);
		$this->parser->init();
		$receiveContent = '';
		while(true)
		{
			while('' !== $receiveContent .= socket_read($socket, 2048))
			{
				if("\r\n" !== substr($receiveContent, -2, 2))
				{
					continue;
				}
				$data = $this->parser->receive($receiveContent);
				$receiveContent = '';
			}
		}
		socket_close($socket);
	}
}