<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Yurun\Until\Event;

// 监听事件
Event::on('test', function($e){
	var_dump('trigger test', $e);
	$e['value'] = 'yurun';
});

// 触发事件
Event::trigger('test', array('message'=>'666', 'value'=>&$value));
var_dump('value:',$value);

/* 一次性事件 */
// 监听事件
Event::once('test1', function($e){
	var_dump('trigger test', $e);
	$e['value'] = $e['message'];
});

// 触发事件1
Event::trigger('test1', array('message'=>'666', 'value'=>&$value));
var_dump('value:',$value);
$value = null;
// 触发事件2
Event::trigger('test1', array('message'=>'777', 'value'=>&$value));
var_dump('value:',$value);