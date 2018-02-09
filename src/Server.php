<?php
namespace Yurun\Proxy;

class Server
{
	private $option;

	private $httpServer;
	
	private $listenServer;

	private $parser;

	public function __construct($option)
	{
		$this->option = $option;
	}

	public function start()
	{
		//创建Server对象，监听端口
		$this->httpServer = new \swoole_http_server($this->option['web']['ip'], $this->option['web']['port']);

		$this->httpServer->set([
			'upload_tmp_dir'	=>	'/' === substr($this->option['upload_tmp_dir'], 0, 1) ? $this->option['upload_tmp_dir'] : dirname(__DIR__) . '/' . $this->option['upload_tmp_dir'],
		]);

		$this->httpServer->on('request', function($request, $response){
			$this->parser->request($request, $response);
		});

		$this->listenServer = $this->httpServer->listen($this->option['listen']['ip'], $this->option['listen']['port'], SWOOLE_SOCK_TCP);
		$this->listenServer->set(array(
			'open_eof_check'	=>	true,
			'package_eof' 		=> "\r\n",
		));

		//监听连接进入事件
		$this->listenServer->on('connect', function($serv, $fd){
			$this->onConnect($serv, $fd);
		});

		//监听数据接收事件
		$this->listenServer->on('receive', function ($serv, $fd, $from_id, $data) {
			$this->parser->receive($serv, $fd, $from_id, $data);
		});

		//监听连接关闭事件
		$this->listenServer->on('close', function ($serv, $fd) {
			$this->parser->close($serv, $fd);
		});

		$this->parser = new ServerParser($this->httpServer);

		//启动服务器
		$this->httpServer->start();

		echo '123';
	}

	private function onConnect($serv, $fd)
	{
		echo "Client: Connect.\n";
		// $timerID = swoole_timer_tick(1000, function() use($fd, &$timerID){
		// 	if($this->httpServer->exist($fd))
		// 	{
		// 		$this->httpServer->send($fd, $this->atomic->add(1));
		// 	}
		// 	else
		// 	{
		// 		swoole_timer_clear($timerID);
		// 	}
		// });
	}

	private function onClose($serv, $fd)
	{
		echo "Client: Close.\n";
	}
}