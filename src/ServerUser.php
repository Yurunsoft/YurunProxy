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
		$this->lock->lock();
		$this->domain = $domain;
		$this->lock->unlock();
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
		$this->httpServer->send($this->fd, json_encode([
			'a'			=>	ServerAction::RECEIVE_HTTP_REQUEST,
			'id'		=>	$id,
			'request'	=>	$request,
			'files'		=>	$files,
		]) . "\r\n");
	}

	public function parseHttpResponse($data)
	{
		if(isset($this->requests[$data['id']]))
		{
			$response = $this->requests[$data['id']]['response'];
			foreach($data['header'] as $name => $value)
			{
				if(!in_array($name, ['Content-Encoding', 'Transfer-Encoding']))
				{
					$response->header($name, $value);
				}
			}
			if(isset($data['header']['Content-Encoding']))
			{
				$response->gzip(5);
			}
			$response->end(gzuncompress(base64_decode($data['response'])));
			$this->lock->lock();
			unset($this->requests[$data['id']]);
			$this->lock->unlock();
		}
	}
}