<?php
return [
	// 网页服务
	'web'	=>	[
		// ip
		'ip'	=>	'0.0.0.0',
		// 端口
		'port'	=>	80,
	],
	// 服务监听
	'listen'	=>	[
		// ip
		'ip'	=>	'0.0.0.0',
		// 端口
		'port'	=>	9899,
		// 服务密钥
		'key'	=>	'4BA9E837899C871693A474E0A9F8456C',
	],
	// 上传文件临时目录
	'upload_tmp_dir'	=>	'tmp',
	// 支持代理的域名，支持多个
	'domain'	=>	[
		// 域名
		'www.proxy.com'		=>	[
			// 是否启用
			'enabled'	=>	true,
			// 域名密钥
			'key'		=>	'6EDBADE2B46E8F333BC17F0B0E60C1B3',
		],
	],
];