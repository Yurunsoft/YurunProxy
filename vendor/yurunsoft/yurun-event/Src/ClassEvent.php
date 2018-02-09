<?php
namespace Yurun\Until;

trait ClassEvent
{
	/**
	 * 事件绑定记录
	 */
	protected $events = array();

	/**
	 * 注册事件
	 * @param string $event
	 * @param mixed $callback
	 * @param bool $first 是否优先执行，以靠后设置的为准
	 */
	public function register($event, $callback, $first = false, $once = false)
	{
		if (!isset($this->events[$event]))
		{
			$this->events[$event] = array();
		}
		$item = array(
			'callback'	=>	$callback,
			'once'		=>	$once,
		);
		if($first)
		{
			array_unshift($this->events[$event], $item);
		}
		else 
		{
			$this->events[$event][] = $item;
		}
	}

	/**
	 * 注册事件，register的别名
	 * @param string $event
	 * @param mixed $callback
	 * @param bool $first 是否优先执行，以靠后设置的为准
	 */
	public function on($event, $callback, $first = false)
	{
		$this->register($event, $callback, $first);
	}

	/**
	 * 注册一次性事件
	 * @param string $event
	 * @param mixed $callback
	 * @param boolean $first
	 */
	public function once($event, $callback, $first = false)
	{
		$this->register($event, $callback, $first, true);
	}
	
	/**
	 * 触发事件(监听事件)
	 * @param name $event 
	 * @param boolean $once        	
	 * @return mixed
	 */
	protected function trigger($event, $params = array())
	{
		if (isset($this->events[$event]))
		{
			foreach ($this->events[$event] as $key => $item)
			{
				if(true === $item['once'])
				{
					unset($this->events[$event][$key]);
				}
				if(true === call_user_func($item['callback'], $params))
				{
					// 事件返回true时不继续执行其余事件
					return true;
				}
			}
			return false;
		}
		return true;
	}
}