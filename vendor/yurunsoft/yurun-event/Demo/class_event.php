<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use Yurun\Until\ClassEvent;

class Test
{
	use ClassEvent;

	private $value;

	public function setValue($value)
	{
		$this->value = $value;
		$this->trigger('changeValue', array('value'=>$value));
	}
}

$test = new Test;
// 绑定事件
$test->on('changeValue', function($e){
	echo 'changeValue1:', $e['value'], PHP_EOL;
});
// 一次性事件
$test->once('changeValue', function($e){
	echo 'changeValue2:', $e['value'], PHP_EOL;
});
$test->setValue(123);
$test->setValue(456);