<?php
namespace Yurun\Proxy;

class Client extends Base
{
	private $parser;

	public function __construct($option)
	{
		parent::__construct();
		static::$option = $option;
	}

	public function start()
	{
		while(true)
		{
			$socket = \socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if(false === $socket)
			{
				exit("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . PHP_EOL);
			}
			$result = socket_connect($socket, static::$option['server']['ip'], static::$option['server']['port']);
			if($result)
			{
				echo 'Info: Connect success', PHP_EOL;
				$this->parser = new ClientParser($socket, static::$option);
				$this->parser->init();
				$receiveContent = '';
				while(false !== $receiveResult = socket_read($socket, 2048, PHP_NORMAL_READ))
				{
					$receiveContent .= $receiveResult;
					if("\r\n" !== substr($receiveContent, -2, 2))
					{
						continue;
					}
					$data = $this->parser->receive($receiveContent);
					$receiveContent = '';
				}
				echo "Error: ", socket_strerror(socket_last_error()), PHP_EOL;
			}
			else
			{
				echo "socket_connect() failed: reason: ", socket_strerror(socket_last_error()), PHP_EOL;
			}
			socket_close($socket);
			echo 'Info: Wait ', static::$option['retry_timespan'], ' seconds for reconnect', PHP_EOL;
			sleep(static::$option['retry_timespan']);
		}
	}
}