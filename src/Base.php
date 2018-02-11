<?php
namespace Yurun\Proxy;

class Base
{
	public static $option;

	/**
	 * 命令行参数
	 * @var array
	 */
	public static $cmdParams;

	public function __construct()
	{
		$this->parseCliArgs();
	}

	/**
	 * 处理cli参数
	 */
	private static function parseCliArgs()
	{
		static::$cmdParams = array();
		$keyName = null;
		for($i = 1; $i < $_SERVER['argc']; ++$i)
		{
			if(isset($_SERVER['argv'][$i][0]) && '-' === $_SERVER['argv'][$i][0])
			{
				$keyName = substr($_SERVER['argv'][$i],1);
				static::$cmdParams[$keyName] = true;
			}
			else
			{
				if(null === $keyName)
				{
					static::$cmdParams[$_SERVER['argv'][$i]] = true;
				}
				else
				{
					static::$cmdParams[$keyName] = $_SERVER['argv'][$i];
					$keyName = null;
				}
			}
		}
	}
}