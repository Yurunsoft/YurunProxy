<?php
namespace Yurun\Proxy;

use \Yurun\Proxy\Consts\ServerAction;

class ServerUser
{
	public $fd;

	public $httpServer;

	public $lock;

	public $domain;

	public $requests = [];
	
	public function __construct($fd, $httpServer)
	{
		$this->fd = $fd;
		$this->httpServer = $httpServer;
		$this->lock = new \swoole_lock(SWOOLE_MUTEX);
	}

	public function setDomain($domain)
	{
		foreach($domain as $tDomain)
		{
			if(!isset(Server::$option['domain'][$tDomain]) || !Server::$option['domain'][$tDomain]['enabled'])
			{
				$this->sendData(ServerAction::INIT_RESPONSE, [
					'success'	=>	false,
					'message'	=>	'Domain "' . $tDomain . '" not enabled in config.',
				]);
				return false;
			}
		}
		$this->lock->lock();
		$this->domain = $domain;
		$this->lock->unlock();
		return true;
	}

	public function addHttpRequest($id, $request, $response)
	{
		$this->lock->lock();
		$this->requests[$id] = [
			'request'	=>	$request,
			'response'	=>	$response,
		];
		$this->lock->unlock();
		$files = [];
		if(!empty($request->files))
		{
			foreach($request->files as $name => $item)
			{
				$files[$name] = [
					'fileName'	=>	$item['name'],
					'content'	=>	base64_encode(file_get_contents($item['tmp_name'])),
				];
			}
		}
		$this->sendData(ServerAction::RECEIVE_HTTP_REQUEST, [
			'id'		=>	$id,
			'request'	=>	$request,
			'files'		=>	$files,
		]);
	}

	public function parseHttpResponse($data)
	{
		if(isset($this->requests[$data['id']]))
		{
			$this->decryptData($data);
			$response = $this->requests[$data['id']]['response'];
			if(null === $data['data'])
			{
				$response->end('Client Error!');
			}
			else
			{
				foreach($data['data']['header'] as $name => $value)
				{
					if(!in_array($name, ['Content-Encoding', 'Transfer-Encoding']))
					{
						$response->header($name, $value);
					}
				}
				if(isset($data['data']['header']['Content-Encoding']))
				{
					$response->gzip(5);
				}
				$response->end(gzuncompress(base64_decode($data['data']['response'])));
			}
			$this->lock->lock();
			unset($this->requests[$data['id']]);
			$this->lock->unlock();
		}
	}

	public function sendData($action, $data = [])
	{
		$data = [
			'a'		=>	$action,
			'data'	=>	$data,
		];
		$data = \json_encode($data) . "\r\n";
		$this->httpServer->send($this->fd, $data);
	}

	public function decryptData(&$data)
	{
		list($domain, ) = explode(':', $this->requests[$data['id']]['request']->header['host']);
		$data['data'] = json_decode(\Yurun\Proxy\Encrypt\AES::decrypt(gzuncompress(base64_decode($data['data'])), Server::$option['domain'][$domain]['key']), true);
	}
}