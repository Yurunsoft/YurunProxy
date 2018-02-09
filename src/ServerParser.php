<?php
namespace Yurun\Proxy;

use \Yurun\Proxy\Consts\ServerAction;

class ServerParser
{
	public $atomic;

	public $lock;

	public $userList = [];

	public $domainUserRelation = [];

	public $httpServer;
	
	public function __construct($httpServer)
	{
		$this->httpServer = $httpServer;
		$this->atomic = new \swoole_atomic(0);
		$this->lock = new \swoole_lock(SWOOLE_MUTEX);
	}

	public function receive($serv, $fd, $from_id, $data)
	{
		$data = json_decode(gzuncompress(substr($data, 0, -2)), true);
		switch($data['a'])
		{
			case ServerAction::INIT:
				$this->lock->lock();
				$user = new ServerUser($fd, $this->httpServer);
				$this->userList[$fd] = $user;
				foreach($data['domain'] as $domain)
				{
					$this->domainUserRelation[$domain] = $user;
				}
				$this->lock->unlock();
				$user->setDomain($data['domain']);
				echo 'user_count:', count($this->userList), PHP_EOL;
				break;
			case ServerAction::RECEIVE_HTTP_RESPONSE:
				// echo 'http_response:', json_encode($data), PHP_EOL;
				$this->userList[$fd]->parseHttpResponse($data);
				break;
		}
	}

	public function close($serv, $fd)
	{
		$this->lock->lock();
		if(isset($this->userList[$fd]))
		{
			foreach($this->domainUserRelation as $domain => $user)
			{
				if($user->fd === $fd)
				{
					unset($this->domainUserRelation[$domain]);
				}
			}
			unset($this->userList[$fd]);
		}
		$this->lock->unlock();
		echo 'user_count:', count($this->userList), PHP_EOL;
	}

	public function request($request, $response)
	{
		echo 'request', PHP_EOL;
		// var_dump($request, $response);
		list($domain, ) = explode(':', $request->header['host']);
		if(isset($this->domainUserRelation[$domain]))
		{
			$user = $this->domainUserRelation[$domain];
			$user->addHttpRequest($this->atomic->add(1), $request, $response);
		}
		else
		{
			$response->end('area you ok?');
		}
	}
}