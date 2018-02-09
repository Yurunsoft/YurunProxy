<?php
namespace Yurun\Until;

class Event
{
	/**
	 * 事件绑定记录
	 */
	private static $events = array();
	
	/**
	 * 注册事件
	 * @param string $event
	 * @param mixed $callback
	 * @param bool $first 是否优先执行，以靠后设置的为准
	 */
	public static function register($event, $callback, $first = false, $once = false)
	{
		if (!isset(self::$events[$event]))
		{
			self::$events[$event] = array();
		}
		$item = array(
			'callback'	=>	$callback,
			'once'		=>	$once,
		);
		if($first)
		{
			array_unshift(self::$events[$event], $item);
		}
		else 
		{
			self::$events[$event][] = $item;
		}
	}

	/**
	 * 注册事件，register的别名
	 * @param string $event
	 * @param mixed $callback
	 * @param bool $first 是否优先执行，以靠后设置的为准
	 */
	public static function on($event, $callback, $first = false)
	{
		self::register($event, $callback, $first);
	}

	/**
	 * 注册一次性事件
	 * @param string $event
	 * @param mixed $callback
	 * @param boolean $first
	 */
	public static function once($event, $callback, $first = false)
	{
		self::register($event, $callback, $first, true);
	}
	
	/**
	 * 触发事件(监听事件)
	 * @param name $event        	
	 * @param boolean $once        	
	 * @return mixed
	 */
	public static function trigger($event, $params = array())
	{
		if (isset(self::$events[$event]))
		{
			foreach (self::$events[$event] as $key => $item)
			{
				if(true === $item['once'])
				{
					unset(self::$events[$event][$key]);
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