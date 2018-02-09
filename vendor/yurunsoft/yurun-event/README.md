# YurunEvent
PHP事件类，支持全局事件和类中事件。

# Composer

```json
"require": {
    "yurunsoft/yurun-event" : "dev-master"
}
```
# 代码实例

## 全局事件

```php
// 监听事件
Event::on('test', function($e){
	var_dump('trigger test', $e);
	$e['value'] = 'yurun';
});

// 一次性事件
Event::once('test1', function($e){
	var_dump('trigger test', $e);
	$e['value'] = $e['message'];
});

// 触发事件
Event::trigger('test', array('message'=>'666', 'value'=>&$value));
```

## 类中事件

```php
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
```

更详细的代码请至Demo目录。