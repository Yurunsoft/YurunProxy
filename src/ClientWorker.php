<?php
namespace Yurun\Proxy;

use \Yurun\Proxy\Consts\ServerAction;

class ClientWorker
{
	public $socket;

	public $domain;

	public function __construct($socket)
	{
		$this->socket = $socket;
	}

	public function init()
	{
		if(isset(Client::$cmdParams['domain']))
		{
			if(isset(Client::$option['domain'][Client::$cmdParams['domain']]))
			{
				$this->domain = [
					Client::$cmdParams['domain']
				];
			}
			else
			{
				exit('Error: Domain "' . Client::$cmdParams['domain'] . '" not found in "config/client.php".');
			}
		}
		else
		{
			$this->domain = \array_keys(Client::$option['domain']);
		}
		$this->sendData(ServerAction::INIT, [
			'domain'	=>	$this->domain,
		]);
	}

	public function receiveHttpRequest($data)
	{
		list($domain, ) = explode(':', $data['request']['header']['host']);
		if(!in_array($domain, $this->domain))
		{
			$this->sendData(ServerAction::RECEIVE_HTTP_RESPONSE, [
				'id'		=>	$data['id'],
				'success'	=>	false,
			]);
			return;
		}
		$http = new \Yurun\Util\HttpRequest;
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
		$response = $http->header('Host', $domain . ':' . Client::$option['domain'][$domain]['port'])
							->send('http://' . Client::$option['domain'][$domain]['ip'] . ':' . Client::$option['domain'][$domain]['port'] . '/' . $data['request']['server']['request_uri'] . (empty($data['request']['get']) ? '' : ('?' . http_build_query($data['request']['get']))), $post, $method);
		$header = $response->headers;
		$this->sendData(ServerAction::RECEIVE_HTTP_RESPONSE, [
			'id'		=>	$data['id'],
			'success'	=>	true,
			'response'	=>	base64_encode(gzcompress($response->body)),
			'header'	=>	$header,
		],  Client::$option['domain'][$domain]['key']);
	}

	public function initResponse($data)
	{
		if(!$data['success'])
		{
			exit('Error: ' . $data['message']);
		}
	}

	public function sendData($action, $data = [], $dataEncryptKey = null)
	{
		$d = [
			'a'		=>	$action,
		];
		if(isset($data['id']))
		{
			$d['id'] = $data['id'];
			unset($data['id']);
		}
		if(null === $dataEncryptKey)
		{
			$d['data'] = base64_encode(gzcompress(\json_encode($data)));
		}
		else
		{
			$d['data'] = base64_encode(gzcompress(\Yurun\Proxy\Encrypt\AES::encrypt(\json_encode($data), $dataEncryptKey)));
		}
		$d = gzcompress(\Yurun\Proxy\Encrypt\AES::encrypt(\json_encode($d), Client::$option['server']['key'])) . "\r\n";
		$result = \socket_write($this->socket, $d, \strlen($d));
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

}