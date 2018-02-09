<?php
namespace Yurun\Proxy;

use \Yurun\Proxy\Consts\ServerAction;
use Yurun\Util\HttpRequestMultipartBody;

class ClientParser
{
	public $socket;

	public $option;

	public function __construct($socket, $option)
	{
		$this->socket = $socket;
		$this->option = $option;
	}

	public function init()
	{
		$this->sendData(ServerAction::INIT, [
			'domain'	=>	\array_keys($this->option['domain']),
		]);
	}

	public function receive($data)
	{
		$data = $this->parseData($data);
		// var_dump($data['files']);
		switch($data['a'])
		{
			case ServerAction::RECEIVE_HTTP_REQUEST:
				$http = new \Yurun\Util\HttpRequest;
				list($domain, ) = explode(':', $data['request']['header']['host']);
				$method = $data['request']['server']['request_method'];
				$header = $data['request']['header'];
				unset($header['connection'], $header['transfer-encoding'], $header['content-length'], $header['keep-alive'], $header['host'], $header['content-length']);
				if(empty($data['request']['files']))
				{
					$post = $data['request']['post'];
				}
				else
				{
					if(isset($data['request']['header']['content-type']))
					{
						unset($header['content-type']);
					}
					$post = new HttpRequestMultipartBody;
					if(null !== $data['request']['post'])
					{
						foreach($data['request']['post'] as $key => $value)
						{
							$post->add($key, $value);
						}
					}
					foreach($data['files'] as $name => $item)
					{
						$post->addFileContent($name, base64_decode($item['content']), $item['fileName']);
					}
				}
				foreach($header as $name => $value)
				{
					$http->header($name, $value);
				}
				if(null !== $data['request']['cookie'])
				{
					$http->cookies($data['request']['cookie']);
				}
				$response = $http->header('Host', $domain . ':' . $this->option['domain'][$domain]['port'])
								 ->send('http://' . $this->option['domain'][$domain]['ip'] . ':' . $this->option['domain'][$domain]['port'] . '/' . $data['request']['server']['request_uri'] . (empty($data['request']['get']) ? '' : ('?' . http_build_query($data['request']['get']))), $post, $method);
				$header = $response->headers;
				$this->sendData(ServerAction::RECEIVE_HTTP_RESPONSE, [
					'id'		=>	$data['id'],
					'response'	=>	base64_encode(gzcompress($response->body)),
					'header'	=>	$header,
				]);
				break;
		}
	}

	public function sendData($action, $data = [])
	{
		$data['a'] = $action;
		$data = gzcompress(\json_encode($data)) . "\r\n";
		$result = \socket_write($this->socket, $data, \strlen($data));
		var_dump(\strlen($data), $result);
		if(false === $result)
		{
			echo "socket_write() failed: reason: " . \socket_strerror(\socket_last_error()) . PHP_EOL;
			return false;
		}
		else
		{
			return true;
		}
	}

	public function parseData($data)
	{
		return json_decode(substr($data, 0, -2), true);
	}
}